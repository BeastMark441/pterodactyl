<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Pterodactyl\Models\Task;
use Illuminate\Http\Response;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\Permission;
use Illuminate\Database\ConnectionInterface;
use Pterodactyl\Repositories\Eloquent\TaskRepository;
use Pterodactyl\Exceptions\Http\HttpForbiddenException;
use Pterodactyl\Transformers\Api\Client\TaskTransformer;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Exceptions\Service\ServiceLimitExceededException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pterodactyl\Http\Requests\Api\Client\Servers\Schedules\StoreTaskRequest;

class ScheduleTaskController extends ClientApiController
{
    /**
     * Конструктор ScheduleTaskController.
     */
    public function __construct(
        private ConnectionInterface $connection,
        private TaskRepository $repository
    ) {
        parent::__construct();
    }

    /**
     * Создает новую задачу для данного расписания и сохраняет ее в базе данных.
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Service\ServiceLimitExceededException
     */
    public function store(StoreTaskRequest $request, Server $server, Schedule $schedule): array
    {
        $limit = config('pterodactyl.client_features.schedules.per_schedule_task_limit', 10);
        if ($schedule->tasks()->count() >= $limit) {
            throw new ServiceLimitExceededException("Расписания не могут иметь более $limit задач, связанных с ними. Создание этой задачи превысит лимит.");
        }

        if ($server->backup_limit === 0 && $request->action === 'backup') {
            throw new HttpForbiddenException("Задача резервного копирования не может быть создана, когда лимит резервного копирования сервера установлен на 0.");
        }

        /** @var \Pterodactyl\Models\Task|null $lastTask */
        $lastTask = $schedule->tasks()->orderByDesc('sequence_id')->first();

        /** @var \Pterodactyl\Models\Task $task */
        $task = $this->connection->transaction(function () use ($request, $schedule, $lastTask) {
            $sequenceId = ($lastTask->sequence_id ?? 0) + 1;
            $requestSequenceId = $request->integer('sequence_id', $sequenceId);

            // Убедитесь, что идентификатор последовательности не менее 1.
            if ($requestSequenceId < 1) {
                $requestSequenceId = 1;
            }

            // Если идентификатор последовательности из запроса больше или равен следующему доступному
            // идентификатору последовательности, нам не нужно ничего делать. В противном случае нам нужно обновить
            // идентификатор последовательности всех задач, которые больше или равны идентификатору последовательности запроса,
            // чтобы он был на один больше текущего значения.
            if ($requestSequenceId < $sequenceId) {
                $schedule->tasks()
                    ->where('sequence_id', '>=', $requestSequenceId)
                    ->increment('sequence_id');
                $sequenceId = $requestSequenceId;
            }

            return $this->repository->create([
                'schedule_id' => $schedule->id,
                'sequence_id' => $sequenceId,
                'action' => $request->input('action'),
                'payload' => $request->input('payload') ?? '',
                'time_offset' => $request->input('time_offset'),
                'continue_on_failure' => $request->boolean('continue_on_failure'),
            ]);
        });

        Activity::event('server:task.create')
            ->subject($schedule, $task)
            ->property(['name' => $schedule->name, 'action' => $task->action, 'payload' => $task->payload])
            ->log();

        return $this->fractal->item($task)
            ->transformWith($this->getTransformer(TaskTransformer::class))
            ->toArray();
    }

    /**
     * Обновляет данную задачу для сервера.
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function update(StoreTaskRequest $request, Server $server, Schedule $schedule, Task $task): array
    {
        if ($schedule->id !== $task->schedule_id || $server->id !== $schedule->server_id) {
            throw new NotFoundHttpException();
        }

        if ($server->backup_limit === 0 && $request->action === 'backup') {
            throw new HttpForbiddenException("Задача резервного копирования не может быть создана, когда лимит резервного копирования сервера установлен на 0.");
        }

        $this->connection->transaction(function () use ($request, $schedule, $task) {
            $sequenceId = $request->integer('sequence_id', $task->sequence_id);
            // Убедитесь, что идентификатор последовательности не менее 1.
            if ($sequenceId < 1) {
                $sequenceId = 1;
            }

            // Переместите все другие задачи в расписании вверх или вниз, чтобы освободить место для новой задачи.
            if ($sequenceId < $task->sequence_id) {
                $schedule->tasks()
                    ->where('sequence_id', '>=', $sequenceId)
                    ->where('sequence_id', '<', $task->sequence_id)
                    ->increment('sequence_id');
            } elseif ($sequenceId > $task->sequence_id) {
                $schedule->tasks()
                    ->where('sequence_id', '>', $task->sequence_id)
                    ->where('sequence_id', '<=', $sequenceId)
                    ->decrement('sequence_id');
            }

            $this->repository->update($task->id, [
                'sequence_id' => $sequenceId,
                'action' => $request->input('action'),
                'payload' => $request->input('payload') ?? '',
                'time_offset' => $request->input('time_offset'),
                'continue_on_failure' => $request->boolean('continue_on_failure'),
            ]);
        });

        Activity::event('server:task.update')
            ->subject($schedule, $task)
            ->property(['name' => $schedule->name, 'action' => $task->action, 'payload' => $task->payload])
            ->log();

        return $this->fractal->item($task->refresh())
            ->transformWith($this->getTransformer(TaskTransformer::class))
            ->toArray();
    }

    /**
     * Удаляет данную задачу для расписания. Если в базе данных есть последующие задачи для этого расписания,
     * их идентификаторы последовательности уменьшаются должным образом.
     *
     * @throws \Exception
     */
    public function delete(ClientApiRequest $request, Server $server, Schedule $schedule, Task $task): JsonResponse
    {
        if ($task->schedule_id !== $schedule->id || $schedule->server_id !== $server->id) {
            throw new NotFoundHttpException();
        }

        if (!$request->user()->can(Permission::ACTION_SCHEDULE_UPDATE, $server)) {
            throw new HttpForbiddenException('У вас нет разрешения на выполнение этого действия.');
        }

        $schedule->tasks()
            ->where('sequence_id', '>', $task->sequence_id)
            ->decrement('sequence_id');
        $task->delete();

        Activity::event('server:task.delete')->subject($schedule, $task)->property('name', $schedule->name)->log();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
