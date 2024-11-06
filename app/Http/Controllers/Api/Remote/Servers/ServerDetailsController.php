<?php

namespace Pterodactyl\Http\Controllers\Api\Remote\Servers;

use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Illuminate\Database\ConnectionInterface;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Eggs\EggConfigurationService;
use Pterodactyl\Repositories\Eloquent\ServerRepository;
use Pterodactyl\Http\Resources\Wings\ServerConfigurationCollection;
use Pterodactyl\Services\Servers\ServerConfigurationStructureService;

class ServerDetailsController extends Controller
{
    /**
     * Конструктор ServerConfigurationController.
     */
    public function __construct(
        protected ConnectionInterface $connection,
        private ServerRepository $repository,
        private ServerConfigurationStructureService $configurationStructureService,
        private EggConfigurationService $eggConfigurationService
    ) {
    }

    /**
     * Возвращает информацию о сервере, которая позволяет Wings самовосстанавливаться и обеспечивать
     * соответствие состояния сервера Панели в любое время.
     *
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        $server = $this->repository->getByUuid($uuid);

        return new JsonResponse([
            'settings' => $this->configurationStructureService->handle($server),
            'process_configuration' => $this->eggConfigurationService->handle($server),
        ]);
    }

    /**
     * Перечисляет все серверы с их конфигурациями, которые назначены запрашивающему узлу.
     */
    public function list(Request $request): ServerConfigurationCollection
    {
        /** @var \Pterodactyl\Models\Node $node */
        $node = $request->attributes->get('node');

        // Избегайте неконтролируемых N+1 SQL-запросов, предварительно загружая отношения, которые используются
        // в каждом из вызываемых ниже сервисов.
        $servers = Server::query()->with('allocations', 'egg', 'mounts', 'variables', 'location')
            ->where('node_id', $node->id)
            // Если вы не приведете это к строке, вы получите строковое значение per_page в
            // метаданных, и тогда Wings аварийно завершит работу.
            ->paginate((int) $request->input('per_page', 50));

        return new ServerConfigurationCollection($servers);
    }

    /**
     * Сбрасывает состояние всех серверов на узле в нормальное. Это запускается
     * при перезапуске Wings и полезно для обеспечения того, чтобы любые серверы на узле
     * не застряли неправильно в состояниях установки/восстановления из резервной копии, так как
     * перезагрузка Wings полностью остановит эти процессы.
     *
     * @throws \Throwable
     */
    public function resetState(Request $request): JsonResponse
    {
        $node = $request->attributes->get('node');

        // Получите все серверы, которые в настоящее время помечены как восстанавливающиеся из резервной копии
        // на этом узле, которые также не имеют неудачной резервной копии, отслеживаемой в таблице журналов аудита.
        //
        // Для каждого из этих серверов мы создадим новую запись в журнале аудита, чтобы пометить их как
        // неудачные, а затем обновим их все, чтобы они находились в допустимом состоянии.
        $servers = Server::query()
            ->with([
                'activity' => fn ($builder) => $builder
                    ->where('activity_logs.event', 'server:backup.restore-started')
                    ->latest('timestamp'),
            ])
            ->where('node_id', $node->id)
            ->where('status', Server::STATUS_RESTORING_BACKUP)
            ->get();

        $this->connection->transaction(function () use ($node, $servers) {
            /** @var \Pterodactyl\Models\Server $server */
            foreach ($servers as $server) {
                /** @var \Pterodactyl\Models\ActivityLog|null $activity */
                $activity = $server->activity->first();
                if (!is_null($activity)) {
                    if ($subject = $activity->subjects->where('subject_type', 'backup')->first()) {
                        // Просто создайте новую запись в журнале аудита для этого события и обновите состояние сервера
                        // чтобы действия с питанием, управление файлами и резервное копирование могли возобновиться как обычно.
                        Activity::event('server:backup.restore-failed')
                            ->subject($server, $subject->subject)
                            ->property('name', $subject->subject->name)
                            ->log();
                    }
                }
            }

            // Обновите любой сервер, помеченный как устанавливающийся или восстанавливающийся, в нормальное состояние
            // на этом этапе процесса.
            Server::query()->where('node_id', $node->id)
                ->whereIn('status', [Server::STATUS_INSTALLING, Server::STATUS_RESTORING_BACKUP])
                ->update(['status' => null]);
        });

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
