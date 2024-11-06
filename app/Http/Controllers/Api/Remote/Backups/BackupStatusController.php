<?php

namespace Pterodactyl\Http\Controllers\Api\Remote\Backups;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Pterodactyl\Models\Backup;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Extensions\Backups\BackupManager;
use Pterodactyl\Extensions\Filesystem\S3Filesystem;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Pterodactyl\Http\Requests\Api\Remote\ReportBackupCompleteRequest;

class BackupStatusController extends Controller
{
    /**
     * Конструктор BackupStatusController.
     */
    public function __construct(private BackupManager $backupManager)
    {
    }

    /**
     * Обрабатывает обновление состояния резервной копии.
     *
     * @throws \Throwable
     */
    public function index(ReportBackupCompleteRequest $request, string $backup): JsonResponse
    {
        // Получить узел, связанный с запросом.
        /** @var \Pterodactyl\Models\Node $node */
        $node = $request->attributes->get('node');

        /** @var \Pterodactyl\Models\Backup $model */
        $model = Backup::query()
            ->where('uuid', $backup)
            ->firstOrFail();

        // Убедитесь, что резервная копия "принадлежит" узлу, который делает запрос. Это предотвращает
        // другие узлы от вмешательства в резервные копии, которые им не принадлежат.
        /** @var \Pterodactyl\Models\Server $server */
        $server = $model->server;
        if ($server->node_id !== $node->id) {
            throw new HttpForbiddenException('У вас нет разрешения на доступ к этой резервной копии.');
        }

        if ($model->is_successful) {
            throw new BadRequestHttpException('Невозможно обновить статус резервной копии, которая уже помечена как завершенная.');
        }

        $action = $request->boolean('successful') ? 'server:backup.complete' : 'server:backup.fail';
        $log = Activity::event($action)->subject($model, $model->server)->property('name', $model->name);

        $log->transaction(function () use ($model, $request) {
            $successful = $request->boolean('successful');

            $model->fill([
                'is_successful' => $successful,
                // Измените состояние блокировки на разблокированное, если это была неудачная резервная копия, чтобы ее можно было
                // легко удалить. Также не имеет смысла иметь заблокированную резервную копию в системе,
                // которая не удалась.
                'is_locked' => $successful ? $model->is_locked : false,
                'checksum' => $successful ? ($request->input('checksum_type') . ':' . $request->input('checksum')) : null,
                'bytes' => $successful ? $request->input('size') : 0,
                'completed_at' => CarbonImmutable::now(),
            ])->save();

            // Проверьте, используем ли мы адаптер резервного копирования s3. Если да, убедитесь, что мы правильно
            // отмечаем резервную копию как завершенную в S3.
            $adapter = $this->backupManager->adapter();
            if ($adapter instanceof S3Filesystem) {
                $this->completeMultipartUpload($model, $adapter, $successful, $request->input('parts'));
            }
        });

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Обрабатывает переключение состояния восстановления сервера. Поле состояния сервера должно быть
     * установлено обратно в null, даже если восстановление не удалось. Это не неразрешимое состояние для
     * сервера, и пользователь может продолжать пытаться восстановить или просто использовать кнопку переустановки.
     *
     * Единственное, что делает поле successful, это обновляет значение записи для таблицы аудита
     * отслеживания этого восстановления.
     *
     * @throws \Throwable
     */
    public function restore(Request $request, string $backup): JsonResponse
    {
        /** @var \Pterodactyl\Models\Backup $model */
        $model = Backup::query()->where('uuid', $backup)->firstOrFail();

        $model->server->update(['status' => null]);

        Activity::event($request->boolean('successful') ? 'server:backup.restore-complete' : 'server.backup.restore-failed')
            ->subject($model, $model->server)
            ->property('name', $model->name)
            ->log();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Помечает многочастичную загрузку в данном S3-совместимом экземпляре как неудачную или успешную для
     * данной резервной копии.
     *
     * @throws \Exception
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    protected function completeMultipartUpload(Backup $backup, S3Filesystem $adapter, bool $successful, ?array $parts): void
    {
        // Это действительно не должно происходить, но если это произойдет, не дайте нам стать жертвой
        // веселых сообщений об ошибках Amazon. Просто остановите процесс прямо здесь.
        if (empty($backup->upload_id)) {
            // Неудачная резервная копия не должна здесь выдавать ошибку, это может произойти, если резервная копия
            // сталкивается с ошибкой до того, как мы начнем загрузку. AWS предоставляет инструменты для очистки этих неудачных
            // многочастичных загрузок по мере необходимости.
            if (!$successful) {
                return;
            }

            throw new DisplayException('Невозможно завершить запрос резервного копирования: отсутствует upload_id в модели.');
        }

        $params = [
            'Bucket' => $adapter->getBucket(),
            'Key' => sprintf('%s/%s.tar.gz', $backup->server->uuid, $backup->uuid),
            'UploadId' => $backup->upload_id,
        ];

        $client = $adapter->getClient();
        if (!$successful) {
            $client->execute($client->getCommand('AbortMultipartUpload', $params));

            return;
        }

        // В противном случае отправьте запрос CompleteMultipartUpload.
        $params['MultipartUpload'] = [
            'Parts' => [],
        ];

        if (is_null($parts)) {
            $params['MultipartUpload']['Parts'] = $client->execute($client->getCommand('ListParts', $params))['Parts'];
        } else {
            foreach ($parts as $part) {
                $params['MultipartUpload']['Parts'][] = [
                    'ETag' => $part['etag'],
                    'PartNumber' => $part['part_number'],
                ];
            }
        }

        $client->execute($client->getCommand('CompleteMultipartUpload', $params));
    }
}
