<?php

namespace Pterodactyl\Models;

use Illuminate\Support\Collection;

class Permission extends Model
{
    /**
     * Имя ресурса для этой модели при преобразовании
     * в представление API с использованием fractal.
     */
    public const RESOURCE_NAME = 'subuser_permission';

    /**
     * Константы, определяющие различные доступные разрешения.
     */
    public const ACTION_WEBSOCKET_CONNECT = 'websocket.connect';
    public const ACTION_CONTROL_CONSOLE = 'control.console';
    public const ACTION_CONTROL_START = 'control.start';
    public const ACTION_CONTROL_STOP = 'control.stop';
    public const ACTION_CONTROL_RESTART = 'control.restart';

    public const ACTION_DATABASE_READ = 'database.read';
    public const ACTION_DATABASE_CREATE = 'database.create';
    public const ACTION_DATABASE_UPDATE = 'database.update';
    public const ACTION_DATABASE_DELETE = 'database.delete';
    public const ACTION_DATABASE_VIEW_PASSWORD = 'database.view_password';

    public const ACTION_SCHEDULE_READ = 'schedule.read';
    public const ACTION_SCHEDULE_CREATE = 'schedule.create';
    public const ACTION_SCHEDULE_UPDATE = 'schedule.update';
    public const ACTION_SCHEDULE_DELETE = 'schedule.delete';

    public const ACTION_USER_READ = 'user.read';
    public const ACTION_USER_CREATE = 'user.create';
    public const ACTION_USER_UPDATE = 'user.update';
    public const ACTION_USER_DELETE = 'user.delete';

    public const ACTION_BACKUP_READ = 'backup.read';
    public const ACTION_BACKUP_CREATE = 'backup.create';
    public const ACTION_BACKUP_DELETE = 'backup.delete';
    public const ACTION_BACKUP_DOWNLOAD = 'backup.download';
    public const ACTION_BACKUP_RESTORE = 'backup.restore';

    public const ACTION_ALLOCATION_READ = 'allocation.read';
    public const ACTION_ALLOCATION_CREATE = 'allocation.create';
    public const ACTION_ALLOCATION_UPDATE = 'allocation.update';
    public const ACTION_ALLOCATION_DELETE = 'allocation.delete';

    public const ACTION_FILE_READ = 'file.read';
    public const ACTION_FILE_READ_CONTENT = 'file.read-content';
    public const ACTION_FILE_CREATE = 'file.create';
    public const ACTION_FILE_UPDATE = 'file.update';
    public const ACTION_FILE_DELETE = 'file.delete';
    public const ACTION_FILE_ARCHIVE = 'file.archive';
    public const ACTION_FILE_SFTP = 'file.sftp';

    public const ACTION_STARTUP_READ = 'startup.read';
    public const ACTION_STARTUP_UPDATE = 'startup.update';
    public const ACTION_STARTUP_DOCKER_IMAGE = 'startup.docker-image';

    public const ACTION_SETTINGS_RENAME = 'settings.rename';
    public const ACTION_SETTINGS_REINSTALL = 'settings.reinstall';

    public const ACTION_ACTIVITY_READ = 'activity.read';

    /**
     * Нужно ли использовать временные метки для этой модели.
     */
    public $timestamps = false;

    /**
     * Таблица, связанная с моделью.
     */
    protected $table = 'permissions';

    /**
     * Поля, недоступные для массового заполнения.
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Приведение значений к правильному типу.
     */
    protected $casts = [
        'subuser_id' => 'integer',
    ];

    public static array $validationRules = [
        'subuser_id' => 'required|numeric|min:1',
        'permission' => 'required|string',
    ];

    /**
     * Все разрешения, доступные в системе. Следует использовать self::permissions()
     * для их получения, а не обращаться напрямую к этому массиву, так как он может измениться.
     *
     * @see \Pterodactyl\Models\Permission::permissions()
     */
    protected static array $permissions = [
        'websocket' => [
            'description' => 'Разрешает пользователю подключаться к websocket сервера, предоставляя доступ к просмотру вывода консоли и статистики сервера в реальном времени.',
            'keys' => [
                'connect' => 'Разрешает пользователю подключаться к websocket-экземпляру сервера для получения потока консоли.',
            ],
        ],

        'control' => [
            'description' => 'Разрешения, управляющие возможностью пользователя управлять состоянием питания сервера или отправлять команды.',
            'keys' => [
                'console' => 'Разрешает пользователю отправлять команды серверу через консоль.',
                'start' => 'Разрешает пользователю запускать сервер, если он остановлен.',
                'stop' => 'Разрешает пользователю останавливать сервер, если он запущен.',
                'restart' => 'Разрешает пользователю выполнять перезапуск сервера. Это также позволяет запускать сервер, если он выключен, но не переводить его в полностью остановленное состояние.',
            ],
        ],

        'user' => [
            'description' => 'Разрешения, позволяющие пользователю управлять другими субпользователями на сервере. Они никогда не смогут редактировать собственную учётную запись или назначать разрешения, которых у них самих нет.',
            'keys' => [
                'create' => 'Разрешает пользователю создавать новых субпользователей для сервера.',
                'read' => 'Разрешает пользователю просматривать субпользователей и их разрешения для сервера.',
                'update' => 'Разрешает пользователю изменять других субпользователей.',
                'delete' => 'Разрешает пользователю удалять субпользователя с сервера.',
            ],
        ],

        'file' => [
            'description' => 'Разрешения, управляющие возможностью пользователя изменять файловую систему этого сервера.',
            'keys' => [
                'create' => 'Разрешает пользователю создавать дополнительные файлы и папки через Панель или прямую загрузку.',
                'read' => 'Разрешает пользователю просматривать содержимое каталога, но не просматривать содержимое файлов и не скачивать их.',
                'read-content' => 'Разрешает пользователю просматривать содержимое конкретного файла. Это также позволяет скачивать файлы.',
                'update' => 'Разрешает пользователю изменять содержимое существующего файла или каталога.',
                'delete' => 'Разрешает пользователю удалять файлы или каталоги.',
                'archive' => 'Разрешает пользователю архивировать содержимое каталога, а также распаковывать существующие архивы в системе.',
                'sftp' => 'Разрешает пользователю подключаться по SFTP и управлять файлами сервера с использованием других назначенных файловых разрешений.',
            ],
        ],

        'backup' => [
            'description' => 'Разрешения, управляющие возможностью пользователя создавать резервные копии сервера и управлять ими.',
            'keys' => [
                'create' => 'Разрешает пользователю создавать новые резервные копии этого сервера.',
                'read' => 'Разрешает пользователю просматривать все резервные копии, существующие для этого сервера.',
                'delete' => 'Разрешает пользователю удалять резервные копии из системы.',
                'download' => 'Разрешает пользователю скачивать резервную копию сервера. Опасно: это позволяет пользователю получить доступ ко всем файлам сервера, содержащимся в резервной копии.',
                'restore' => 'Разрешает пользователю восстанавливать сервер из резервной копии. Опасно: в процессе это позволяет удалить все файлы сервера.',
            ],
        ],

        // Управляет разрешениями на редактирование или просмотр аллокаций сервера.
        'allocation' => [
            'description' => 'Разрешения, управляющие возможностью пользователя изменять распределение портов для этого сервера.',
            'keys' => [
                'read' => 'Разрешает пользователю просматривать все аллокации, в данный момент назначенные этому серверу. Пользователи с любым уровнем доступа к этому серверу всегда могут просматривать основную аллокацию.',
                'create' => 'Разрешает пользователю назначать серверу дополнительные аллокации.',
                'update' => 'Разрешает пользователю изменять основную аллокацию сервера и прикреплять заметки к каждой аллокации.',
                'delete' => 'Разрешает пользователю удалять аллокацию с сервера.',
            ],
        ],

        // Управляет разрешениями на редактирование или просмотр параметров запуска сервера.
        'startup' => [
            'description' => 'Разрешения, управляющие возможностью пользователя просматривать параметры запуска этого сервера.',
            'keys' => [
                'read' => 'Разрешает пользователю просматривать переменные запуска сервера.',
                'update' => 'Разрешает пользователю изменять переменные запуска сервера.',
                'docker-image' => 'Разрешает пользователю изменять Docker-образ, используемый при запуске сервера.',
            ],
        ],

        'database' => [
            'description' => 'Разрешения, управляющие доступом пользователя к управлению базами данных этого сервера.',
            'keys' => [
                'create' => 'Разрешает пользователю создавать новую базу данных для этого сервера.',
                'read' => 'Разрешает пользователю просматривать базу данных, связанную с этим сервером.',
                'update' => 'Разрешает пользователю менять пароль для экземпляра базы данных. Если у пользователя нет разрешения view_password, он не увидит обновлённый пароль.',
                'delete' => 'Разрешает пользователю удалять экземпляр базы данных с этого сервера.',
                'view_password' => 'Разрешает пользователю просматривать пароль, связанный с экземпляром базы данных этого сервера.',
            ],
        ],

        'schedule' => [
            'description' => 'Разрешения, управляющие доступом пользователя к управлению планировщиком этого сервера.',
            'keys' => [
                'create' => 'Разрешает пользователю создавать новые планировщики для этого сервера.',
                'read' => 'Разрешает пользователю просматривать планировщики и связанные с ними задачи для этого сервера.',
                'update' => 'Разрешает пользователю изменять планировщики и их задачи для этого сервера.',
                'delete' => 'Разрешает пользователю удалять планировщики для этого сервера.',
            ],
        ],

        'settings' => [
            'description' => 'Разрешения, управляющие доступом пользователя к настройкам этого сервера.',
            'keys' => [
                'rename' => 'Разрешает пользователю переименовывать этот сервер и изменять его описание.',
                'reinstall' => 'Разрешает пользователю запускать переустановку этого сервера.',
            ],
        ],

        'activity' => [
            'description' => 'Разрешения, управляющие доступом пользователя к журналам активности сервера.',
            'keys' => [
                'read' => 'Разрешает пользователю просматривать журналы активности сервера.',
            ],
        ],
    ];

    /**
     * Возвращает все разрешения, доступные в системе для пользователя
     * при управлении сервером.
     */
    public static function permissions(): Collection
    {
        return Collection::make(self::$permissions);
    }
}