<?php

/**
 * Содержит все строки перевода для различных событий журнала активности.
 * Они должны быть привязаны к значению перед двоеточием (:)
 * в имени события. Если двоеточие отсутствует, они должны
 * находиться на верхнем уровне.
 */
return [
    'auth' => [
        'fail' => 'Неудачный вход',
        'success' => 'Вход выполнен',
        'password-reset' => 'Пароль сброшен',
        'reset-password' => 'Запрошен сброс пароля',
        'checkpoint' => 'Запрошена двухфакторная аутентификация',
        'recovery-token' => 'Использован резервный токен двухфакторной аутентификации',
        'token' => 'Пройдена двухфакторная проверка',
        'ip-blocked' => 'Заблокирован запрос с неразрешённого IP-адреса для :identifier',
        'sftp' => [
            'fail' => 'Неудачный вход по SFTP',
        ],
    ],
    'user' => [
		'user' => [
            'create' => 'Создан новый пользователь :email',
        ],
        'account' => [
            'email-changed' => 'Изменён email с :old на :new',
            'password-changed' => 'Изменён пароль',
        ],
        'api-key' => [
            'create' => 'Создан новый API-ключ :identifier',
            'delete' => 'Удалён API-ключ :identifier',
        ],
        'ssh-key' => [
            'create' => 'SSH-ключ :fingerprint добавлен в аккаунт',
            'delete' => 'SSH-ключ :fingerprint удалён из аккаунта',
        ],
        'two-factor' => [
            'create' => 'Двухфакторная аутентификация включена',
            'delete' => 'Двухфакторная аутентификация отключена',
        ],
    ],
    'server' => [
        'reinstall' => 'Сервер переустановлен',
        'console' => [
            'command' => 'Выполнена команда ":command" на сервере',
        ],
        'power' => [
            'start' => 'Сервер запущен',
            'stop' => 'Сервер остановлен',
            'restart' => 'Сервер перезапущен',
            'kill' => 'Процесс сервера принудительно завершён',
        ],
        'backup' => [
            'download' => 'Скачана резервная копия :name',
            'delete' => 'Удалена резервная копия :name',
            'restore' => 'Восстановлена резервная копия :name (удалённые файлы: :truncate)',
            'restore-complete' => 'Восстановление резервной копии :name завершено',
            'restore-failed' => 'Не удалось завершить восстановление резервной копии :name',
            'start' => 'Начато создание новой резервной копии :name',
            'complete' => 'Резервная копия :name отмечена как завершённая',
            'fail' => 'Резервная копия :name отмечена как неудачная',
            'lock' => 'Резервная копия :name заблокирована',
            'unlock' => 'Резервная копия :name разблокирована',
        ],
        'database' => [
            'create' => 'Создана новая база данных :name',
            'rotate-password' => 'Пароль для базы данных :name изменён',
            'delete' => 'База данных :name удалена',
        ],
        'file' => [
            'compress_one' => 'Сжат файл :directory:files.0',
            'compress_other' => 'Сжато :count файлов в :directory',
            'read' => 'Просмотрено содержимое файла :file',
            'copy' => 'Создана копия файла :file',
            'create-directory' => 'Создана директория :directory:name',
            'decompress' => 'Распакованы :files в :directory',
            'delete_one' => 'Удалён :directory:files.0',
            'delete_other' => 'Удалено :count файлов в :directory',
            'download' => 'Скачан файл :file',
            'pull' => 'Удалённый файл с :url загружен в :directory',
            'rename_one' => 'Файл :directory:files.0.from переименован в :directory:files.0.to',
            'rename_other' => 'Переименовано :count файлов в :directory',
            'write' => 'Записано новое содержимое в :file',
            'upload' => 'Начата загрузка файла',
            'uploaded' => 'Загружен :directory:file',
        ],
        'sftp' => [
            'denied' => 'SFTP-доступ заблокирован из-за прав доступа',
            'create_one' => 'Создан :files.0',
            'create_other' => 'Создано :count новых файлов',
            'write_one' => 'Изменено содержимое :files.0',
            'write_other' => 'Изменено содержимое :count файлов',
            'delete_one' => 'Удалён :files.0',
            'delete_other' => 'Удалено :count файлов',
            'create-directory_one' => 'Создана директория :files.0',
            'create-directory_other' => 'Создано :count директорий',
            'rename_one' => ':files.0.from переименован в :files.0.to',
            'rename_other' => 'Переименовано или перемещено :count файлов',
        ],
        'allocation' => [
            'create' => 'Выделение :allocation добавлено к серверу',
            'notes' => 'Заметки для :allocation изменены с ":old" на ":new"',
            'primary' => ':allocation назначено основным выделением сервера',
            'delete' => 'Выделение :allocation удалено',
        ],
        'schedule' => [
            'create' => 'Создан планировщик :name',
            'update' => 'Планировщик :name обновлено',
            'execute' => 'Планировщик :name выполнено вручную',
            'delete' => 'Планировщик :name удалено',
        ],
        'task' => [
            'create' => 'Создана новая задача ":action" для планировщика :name',
            'update' => 'Задача ":action" для планировщика :name обновлена',
            'delete' => 'Удалена задача для планировщика :name',
        ],
        'settings' => [
            'rename' => 'Сервер переименован с :old на :new',
            'description' => 'Описание сервера изменено с :old на :new',
        ],
        'startup' => [
            'edit' => 'Переменная :variable изменена с ":old" на ":new"',
            'image' => 'Docker-образ сервера обновлён с :old на :new',
        ],
        'subuser' => [
            'create' => ':email добавлен как субпользователь',
            'update' => 'Права субпользователя для :email обновлены',
            'delete' => ':email удалён из субпользователей',
        ],
    ],
];