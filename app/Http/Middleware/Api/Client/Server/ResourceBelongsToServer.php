<?php

namespace Pterodactyl\Http\Middleware\Api\Client\Server;

use Illuminate\Http\Request;
use Pterodactyl\Models\Task;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Backup;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Subuser;
use Pterodactyl\Models\Database;
use Pterodactyl\Models\Schedule;
use Pterodactyl\Models\Allocation;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResourceBelongsToServer
{
    /**
     * Проверяет параметры запроса, чтобы определить, принадлежит ли данный ресурс
     * запрашиваемому серверу. Если нет, вызывается ошибка 404.
     *
     * Это критически важно для обеспечения того, чтобы вся последующая логика
     * использовала именно тот сервер, который ожидается, и чтобы мы не обращались
     * к ресурсу, который совершенно не связан с сервером, указанным в запросе.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $params = $request->route()->parameters();
        if (is_null($params) || !$params['server'] instanceof Server) {
            throw new \InvalidArgumentException('Этот middleware не может быть использован в контексте, в котором отсутствует сервер в параметрах.');
        }

        /** @var \Pterodactyl\Models\Server $server */
        $server = $request->route()->parameter('server');
        $exception = new NotFoundHttpException('Запрашиваемый ресурс не найден для этого сервера.');
        foreach ($params as $key => $model) {
            // Специально пропускаем сервер, мы просто пытаемся убедиться, что все
            // остальные ресурсы назначены этому серверу. Также пропускаем все, что
            // в данный момент не является экземпляром Model, так как это в любом случае
            // приведет к 404.
            if ($key === 'server' || !$model instanceof Model) {
                continue;
            }

            switch (get_class($model)) {
                // Все эти модели используют "server_id" в качестве ключевого поля для сервера,
                // к которому они назначены, поэтому логика для них одинакова.
                case Allocation::class:
                case Backup::class:
                case Database::class:
                case Schedule::class:
                case Subuser::class:
                    if ($model->server_id !== $server->id) {
                        throw $exception;
                    }
                    break;
                    // Обычные пользователи являются особым случаем, так как нам нужно убедиться,
                    // что они в данный момент назначены как подпользователи на сервере.
                case User::class:
                    $subuser = $server->subusers()->where('user_id', $model->id)->first();
                    if (is_null($subuser)) {
                        throw $exception;
                    }
                    // Это особый случай, чтобы избежать дополнительного запроса в
                    // основной логике.
                    $request->attributes->set('subuser', $subuser);
                    break;
                    // Задачи являются особым случаем, так как они (в настоящее время) являются
                    // единственным элементом в API, который требует чего-то дополнительно к серверу
                    // для доступа.
                case Task::class:
                    $schedule = $request->route()->parameter('schedule');
                    if ($model->schedule_id !== $schedule->id || $schedule->server_id !== $server->id) {
                        throw $exception;
                    }
                    break;
                default:
                    // Не возвращаем 404 здесь, так как мы хотим убедиться, что никто не полагается
                    // на этот middleware в контексте, в котором он не будет работать. Безопасный отказ.
                    throw new \InvalidArgumentException('Нет обработчика, настроенного для ресурса этого типа: ' . get_class($model));
            }
        }

        return $next($request);
    }
}
