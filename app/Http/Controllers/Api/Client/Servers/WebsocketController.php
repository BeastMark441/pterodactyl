<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Carbon\CarbonImmutable;
use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Permission;
use Pterodactyl\Services\Nodes\NodeJWTService;
use Pterodactyl\Exceptions\Http\HttpForbiddenException;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;
use Pterodactyl\Services\Servers\GetUserPermissionsService;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;

class WebsocketController extends ClientApiController
{
    /**
     * Конструктор WebsocketController.
     */
    public function __construct(
        private NodeJWTService $jwtService,
        private GetUserPermissionsService $permissionsService
    ) {
        parent::__construct();
    }

    /**
     * Генерирует одноразовый токен, который отправляется в каждом вызове вебсокета к Daemon.
     * Это подписанный JWT, который Daemon затем использует для проверки личности пользователя,
     * и позволяет нам постоянно обновлять этот токен и избегать неправильного поддержания сеансов пользователями,
     * а также гарантировать, что пользователи выполняют только те действия, которые им разрешены.
     */
    public function __invoke(ClientApiRequest $request, Server $server): JsonResponse
    {
        $user = $request->user();
        if ($user->cannot(Permission::ACTION_WEBSOCKET_CONNECT, $server)) {
            throw new HttpForbiddenException('У вас нет разрешения на подключение к вебсокету этого сервера.');
        }

        $permissions = $this->permissionsService->handle($server, $user);

        $node = $server->node;
        if (!is_null($server->transfer)) {
            // Проверьте, есть ли у пользователя разрешения на получение журналов передачи.
            if (!in_array('admin.websocket.transfer', $permissions)) {
                throw new HttpForbiddenException('У вас нет разрешения на просмотр журналов передачи сервера.');
            }

            // Перенаправьте запрос вебсокета на новый узел, если сервер был архивирован.
            if ($server->transfer->archived) {
                $node = $server->transfer->newNode;
            }
        }

        $token = $this->jwtService
            ->setExpiresAt(CarbonImmutable::now()->addMinutes(10))
            ->setUser($request->user())
            ->setClaims([
                'server_uuid' => $server->uuid,
                'permissions' => $permissions,
            ])
            ->handle($node, $user->id . $server->uuid);

        $socket = str_replace(['https://', 'http://'], ['wss://', 'ws://'], $node->getConnectionAddress());

        return new JsonResponse([
            'data' => [
                'token' => $token->toString(),
                'socket' => $socket . sprintf('/api/servers/%s/ws', $server->uuid),
            ],
        ]);
    }
}
