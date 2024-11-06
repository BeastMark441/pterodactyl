<?php

namespace Pterodactyl\Http\Middleware\Api\Daemon;

use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Repositories\Eloquent\NodeRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Pterodactyl\Exceptions\Repository\RecordNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DaemonAuthenticate
{
    /**
     * Маршруты демона, которые следует пропустить для этого middleware.
     */
    protected array $except = [
        'daemon.configuration',
    ];

    /**
     * Конструктор DaemonAuthenticate.
     */
    public function __construct(private Encrypter $encrypter, private NodeRepository $repository)
    {
    }

    /**
     * Проверяет, может ли запрос от демона быть правильно приписан к одному экземпляру узла.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        if (in_array($request->route()->getName(), $this->except)) {
            return $next($request);
        }

        if (is_null($bearer = $request->bearerToken())) {
            throw new HttpException(401, 'Доступ к этой конечной точке должен включать заголовок Authorization.', null, ['WWW-Authenticate' => 'Bearer']);
        }

        $parts = explode('.', $bearer);
        // Убедитесь, что в заголовке указаны все правильные части.
        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            throw new BadRequestHttpException('Заголовок Authorization предоставлен в недопустимом формате.');
        }

        try {
            /** @var \Pterodactyl\Models\Node $node */
            $node = $this->repository->findFirstWhere([
                'daemon_token_id' => $parts[0],
            ]);

            if (hash_equals((string) $this->encrypter->decrypt($node->daemon_token), $parts[1])) {
                $request->attributes->set('node', $node);

                return $next($request);
            }
        } catch (RecordNotFoundException $exception) {
            // Ничего не делаем, мы не хотим раскрывать, что узел вообще не существует.
        }

        throw new AccessDeniedHttpException('У вас нет прав для доступа к этому ресурсу.');
    }
}
