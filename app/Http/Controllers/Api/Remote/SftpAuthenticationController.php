<?php

namespace Pterodactyl\Http\Controllers\Api\Remote;

use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\Permission;
use phpseclib3\Crypt\PublicKeyLoader;
use Pterodactyl\Http\Controllers\Controller;
use phpseclib3\Exception\NoKeyLoadedException;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Pterodactyl\Exceptions\Http\HttpForbiddenException;
use Pterodactyl\Services\Servers\GetUserPermissionsService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Pterodactyl\Http\Requests\Api\Remote\SftpAuthenticationFormRequest;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class SftpAuthenticationController extends Controller
{
    use ThrottlesLogins;

    public function __construct(protected GetUserPermissionsService $permissions)
    {
    }

    /**
     * Аутентифицировать набор учетных данных и вернуть связанные данные сервера
     * для SFTP-соединения на демоне. Это поддерживает как публичные ключи, так и пароли.
     */
    public function __invoke(SftpAuthenticationFormRequest $request): JsonResponse
    {
        $connection = $this->parseUsername($request->input('username'));
        if (empty($connection['server'])) {
            throw new BadRequestHttpException('В запросе не указан действительный идентификатор сервера.');
        }

        if ($this->hasTooManyLoginAttempts($request)) {
            $seconds = $this->limiter()->availableIn($this->throttleKey($request));

            throw new TooManyRequestsHttpException($seconds, "Слишком много попыток входа для этой учетной записи, попробуйте снова через $seconds секунд.");
        }

        $user = $this->getUser($request, $connection['username']);
        $server = $this->getServer($request, $connection['server']);

        if ($request->input('type') !== 'public_key') {
            if (!password_verify($request->input('password'), $user->password)) {
                Activity::event('auth:sftp.fail')->property('method', 'password')->subject($user)->log();

                $this->reject($request);
            }
        } else {
            $key = null;
            try {
                $key = PublicKeyLoader::loadPublicKey(trim($request->input('password')));
            } catch (NoKeyLoadedException) {
                // ничего не делать
            }

            if (!$key || !$user->sshKeys()->where('fingerprint', $key->getFingerprint('sha256'))->exists()) {
                // Мы не ведем журнал здесь из-за того, как работает система SFTP. Эта конечная точка
                // будет вызываться для каждого ключа, предоставленного пользователем, что может быть 4 или 5. Это
                // много ненужного шума в журнале.
                //
                // На данный момент мы будем регистрировать только неудачные попытки из-за неправильного пароля, так как они вряд ли
                // произойдут более одного раза за сеанс для пользователя и, скорее всего, будут полезны
                // для конечного пользователя.
                $this->reject($request, is_null($key));
            }
        }

        $this->validateSftpAccess($user, $server);

        return new JsonResponse([
            'user' => $user->uuid,
            'server' => $server->uuid,
            'permissions' => $this->permissions->handle($server, $user),
        ]);
    }

    /**
     * Находит запрашиваемый сервер и гарантирует, что он принадлежит узлу, из которого
     * исходит этот запрос.
     */
    protected function getServer(Request $request, string $uuid): Server
    {
        return Server::query()
            ->where(fn ($builder) => $builder->where('uuid', $uuid)->orWhere('uuidShort', $uuid))
            ->where('node_id', $request->attributes->get('node')->id)
            ->firstOr(function () use ($request) {
                $this->reject($request);
            });
    }

    /**
     * Находит пользователя с заданным именем пользователя или увеличивает количество попыток входа.
     */
    protected function getUser(Request $request, string $username): User
    {
        return User::query()->where('username', $username)->firstOr(function () use ($request) {
            $this->reject($request);
        });
    }

    /**
     * Разбирает имя пользователя, предоставленное в запросе.
     *
     * @return array{"username": string, "server": string}
     */
    protected function parseUsername(string $value): array
    {
        // Перевернуть строку, чтобы избежать проблем с именами пользователей, содержащими точки.
        $parts = explode('.', strrev($value), 2);

        // Перевернуть строки обратно после их разбора.
        return [
            'username' => strrev(array_get($parts, 1)),
            'server' => strrev(array_get($parts, 0)),
        ];
    }

    /**
     * Отклоняет запрос и увеличивает количество попыток входа.
     */
    protected function reject(Request $request, bool $increment = true): void
    {
        if ($increment) {
            $this->incrementLoginAttempts($request);
        }

        throw new HttpForbiddenException('Учетные данные для авторизации были неверны, попробуйте еще раз.');
    }

    /**
     * Проверяет, должен ли пользователь иметь разрешение на использование SFTP для данного сервера.
     */
    protected function validateSftpAccess(User $user, Server $server): void
    {
        if (!$user->root_admin && $server->owner_id !== $user->id) {
            $permissions = $this->permissions->handle($server, $user);

            if (!in_array(Permission::ACTION_FILE_SFTP, $permissions)) {
                Activity::event('server:sftp.denied')->actor($user)->subject($server)->log();

                throw new HttpForbiddenException('У вас нет разрешения на доступ к SFTP для этого сервера.');
            }
        }

        $server->validateCurrentState();
    }

    /**
     * Получает ключ ограничения для данного запроса.
     */
    protected function throttleKey(Request $request): string
    {
        $username = explode('.', strrev($request->input('username', '')));

        return strtolower(strrev($username[0] ?? '') . '|' . $request->ip());
    }
}
