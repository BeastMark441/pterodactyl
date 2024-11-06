<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Pterodactyl\Models\Server;
use Pterodactyl\Models\Permission;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Pterodactyl\Models\Filters\MultiFieldServerFilter;
use Pterodactyl\Transformers\Api\Client\ServerTransformer;
use Pterodactyl\Http\Requests\Api\Client\GetServersRequest;

class ClientController extends ClientApiController
{
    /**
     * Конструктор ClientController.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Возвращает все серверы, доступные клиенту, делающему API-запрос,
     * включая серверы, к которым пользователь имеет доступ как субпользователь.
     */
    public function index(GetServersRequest $request): array
    {
        $user = $request->user();
        $transformer = $this->getTransformer(ServerTransformer::class);

        // Запускаем построитель запросов и обеспечиваем eager load любых запрашиваемых отношений из запроса.
        $builder = QueryBuilder::for(
            Server::query()->with($this->getIncludesForTransformer($transformer, ['node']))
        )->allowedFilters([
            'uuid',
            'name',
            'description',
            'external_id',
            AllowedFilter::custom('*', new MultiFieldServerFilter()),
        ]);

        $type = $request->input('type');
        // Либо возвращаем все серверы, к которым пользователь имеет доступ, потому что он администратор `?type=admin`, либо
        // просто возвращаем все серверы, к которым пользователь имеет доступ, потому что он владелец или субпользователь сервера.
        // Если передан ?type=admin-all, все серверы в системе будут возвращены пользователю, а не только те, которые он может видеть, потому что он администратор.
        if (in_array($type, ['admin', 'admin-all'])) {
            // Если они не администраторы, но хотят все серверы администраторов, не проваливайте запрос, просто
            // сделайте его запросом, который никогда не вернет никаких результатов.
            if (!$user->root_admin) {
                $builder->whereRaw('1 = 2');
            } else {
                $builder = $type === 'admin-all'
                    ? $builder
                    : $builder->whereNotIn('servers.id', $user->accessibleServers()->pluck('id')->all());
            }
        } elseif ($type === 'owner') {
            $builder = $builder->where('servers.owner_id', $user->id);
        } else {
            $builder = $builder->whereIn('servers.id', $user->accessibleServers()->pluck('id')->all());
        }

        $servers = $builder->paginate(min($request->query('per_page', 50), 100))->appends($request->query());

        return $this->fractal->transformWith($transformer)->collection($servers)->toArray();
    }

    /**
     * Возвращает все разрешения субпользователей, доступные в системе.
     */
    public function permissions(): array
    {
        return [
            'object' => 'system_permissions',
            'attributes' => [
                'permissions' => Permission::permissions(),
            ],
        ];
    }
}
