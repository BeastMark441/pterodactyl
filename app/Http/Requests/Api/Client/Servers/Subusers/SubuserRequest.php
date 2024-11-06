<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\Subusers;

use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Subuser;
use Pterodactyl\Exceptions\Http\HttpForbiddenException;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;
use Pterodactyl\Services\Servers\GetUserPermissionsService;

abstract class SubuserRequest extends ClientApiRequest
{
    protected ?Subuser $model;

    /**
     * Авторизовать запрос и убедиться, что пользователь не пытается изменить себя.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function authorize(): bool
    {
        if (!parent::authorize()) {
            return false;
        }

        $user = $this->route()->parameter('user');
        // Не разрешать пользователю редактировать себя на сервере.
        if ($user instanceof User) {
            if ($user->uuid === $this->user()->uuid) {
                return false;
            }
        }

        // Если это POST-запрос, проверьте, что пользователь может назначить выбранные им разрешения.
        if ($this->method() === Request::METHOD_POST && $this->has('permissions')) {
            $this->validatePermissionsCanBeAssigned(
                $this->input('permissions') ?? []
            );
        }

        return true;
    }

    /**
     * Проверяет, что разрешения, которые мы пытаемся назначить, действительно могут быть назначены
     * пользователем, делающим запрос.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function validatePermissionsCanBeAssigned(array $permissions)
    {
        $user = $this->user();
        /** @var \Pterodactyl\Models\Server $server */
        $server = $this->route()->parameter('server');

        // Если мы корневой администратор или владелец сервера, нет необходимости выполнять эти проверки.
        if ($user->root_admin || $user->id === $server->owner_id) {
            return;
        }

        // В противном случае, получите текущий набор разрешений субпользователя и убедитесь, что
        // разрешения, которые они пытаются назначить, не превышают те, которые они уже имеют.
        /** @var \Pterodactyl\Models\Subuser|null $subuser */
        /** @var \Pterodactyl\Services\Servers\GetUserPermissionsService $service */
        $service = $this->container->make(GetUserPermissionsService::class);

        if (count(array_diff($permissions, $service->handle($server, $user))) > 0) {
            throw new HttpForbiddenException('Невозможно назначить разрешения субпользователю, которые ваш аккаунт не имеет.');
        }
    }
}
