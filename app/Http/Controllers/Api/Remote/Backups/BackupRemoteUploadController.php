<?php

namespace Pterodactyl\Http\Controllers\Api\Remote\Backups;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Pterodactyl\Models\Backup;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Extensions\Backups\BackupManager;
use Pterodactyl\Extensions\Filesystem\S3Filesystem;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BackupRemoteUploadController extends Controller
{
    public const DEFAULT_MAX_PART_SIZE = 5 * 1024 * 1024 * 1024;

    /**
     * Конструктор BackupRemoteUploadController.
     */
    public function __construct(private BackupManager $backupManager)
    {
    }

    /**
     * Возвращает необходимые предварительно подписанные URL для загрузки резервной копии в облачное хранилище S3.
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function __invoke(Request $request, string $backup): JsonResponse
    {
        // Получить узел, связанный с запросом.
        /** @var \Pterodactyl\Models\Node $node */
        $node = $request->attributes->get('node');

        // Получить параметр запроса размера.
        $size = (int) $request->query('size');
        if (empty($size)) {
            throw new BadRequestHttpException('Необходимо указать непустой параметр запроса "size".');
        }

        /** @var \Pterodactyl\Models\Backup $model */
        $model = Backup::query()
            ->where('uuid', $backup)
            ->firstOrFail();

        // Проверить, что резервная копия "принадлежит" узлу, который делает запрос. Это предотвращает вмешательство других узлов в резервные копии, которые им не принадлежат.
        /** @var \Pterodactyl\Models\Server $server */
        $server = $model->server;
        if ($server->node_id !== $node->id) {
            throw new HttpForbiddenException('У вас нет разрешения на доступ к этой резервной копии.');
        }

        // Предотвратить повторную загрузку уже завершенных резервных копий.
        if (!is_null($model->completed_at)) {
            throw new ConflictHttpException('Эта резервная копия уже находится в завершенном состоянии.');
        }

        // Убедиться, что используется адаптер S3.
        $adapter = $this->backupManager->adapter();
        if (!$adapter instanceof S3Filesystem) {
            throw new BadRequestHttpException('Настроенный адаптер резервного копирования не является совместимым с S3.');
        }

        // Путь, по которому будет загружена резервная копия
        $path = sprintf('%s/%s.tar.gz', $model->server->uuid, $model->uuid);

        // Получить клиент S3
        $client = $adapter->getClient();
        $expires = CarbonImmutable::now()->addMinutes(config('backups.presigned_url_lifespan', 60));

        // Параметры для генерации предварительно подписанных URL
        $params = [
            'Bucket' => $adapter->getBucket(),
            'Key' => $path,
            'ContentType' => 'application/x-gzip',
        ];

        $storageClass = config('backups.disks.s3.storage_class');
        if (!is_null($storageClass)) {
            $params['StorageClass'] = $storageClass;
        }

        // Выполнить запрос CreateMultipartUpload
        $result = $client->execute($client->getCommand('CreateMultipartUpload', $params));

        // Получить UploadId из запроса CreateMultipartUpload, это необходимо для создания других предварительно подписанных URL.
        $params['UploadId'] = $result->get('UploadId');

        // Получить настроенный размер части
        $maxPartSize = $this->getConfiguredMaxPartSize();

        // Создать столько предварительно подписанных URL UploadPart, сколько необходимо
        $parts = [];
        for ($i = 0; $i < ($size / $maxPartSize); ++$i) {
            $parts[] = $client->createPresignedRequest(
                $client->getCommand('UploadPart', array_merge($params, ['PartNumber' => $i + 1])),
                $expires
            )->getUri()->__toString();
        }

        // Установить upload_id на резервной копии в базе данных.
        $model->update(['upload_id' => $params['UploadId']]);

        return new JsonResponse([
            'parts' => $parts,
            'part_size' => $maxPartSize,
        ]);
    }

    /**
     * Получить настроенный максимальный размер одной части в многосекционной загрузке.
     *
     * Функция пытается получить настроенное значение из конфигурации.
     * Если значение не указано, будет использовано значение по умолчанию.
     *
     * Обратите внимание, если полученное значение конфигурации не может быть преобразовано в int (0), равно нулю или отрицательное,
     * также будет использовано значение по умолчанию.
     *
     * Значение по умолчанию {@see BackupRemoteUploadController::DEFAULT_MAX_PART_SIZE}.
     */
    private function getConfiguredMaxPartSize(): int
    {
        $maxPartSize = (int) config('backups.max_part_size', self::DEFAULT_MAX_PART_SIZE);
        if ($maxPartSize <= 0) {
            $maxPartSize = self::DEFAULT_MAX_PART_SIZE;
        }

        return $maxPartSize;
    }
}
