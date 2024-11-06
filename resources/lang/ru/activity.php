<?php

/**
 * Contains all of the translation strings for different activity log
 * events. These should be keyed by the value in front of the colon (:)
 * in the event name. If there is no colon present, they should live at
 * the top level.
 */
return [
    'auth' => [
        'fail' => 'Неудачный вход',
        'success' => 'Выполнен вход',
        'password-reset' => 'Сброс пароля',
        'reset-password' => 'Запрошен сброс пароля',
        'checkpoint' => 'Запрошена двухфакторная аутентификация',
        'recovery-token' => 'Использован токен восстановления двухфакторной аутентификации',
        'token' => 'Решена задача двухфакторной аутентификации',
        'ip-blocked' => 'Заблокирован запрос с неразрешенного IP адреса для :identifier',
        'sftp' => [
            'fail' => 'Неудачный вход по SFTP',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => 'Email изменен с :old на :new',
            'password-changed' => 'Пароль изменен',
        ],
        'api-key' => [
            'create' => 'Создан новый API ключ :identifier',
            'delete' => 'Удален API ключ :identifier',
        ],
        'ssh-key' => [
            'create' => 'Добавлен SSH ключ :fingerprint к аккаунту',
            'delete' => 'Удален SSH ключ :fingerprint из аккаунта',
        ],
        'two-factor' => [
            'create' => 'Включена двухфакторная аутентификация',
            'delete' => 'Отключена двухфакторная аутентификация',
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
            'kill' => 'Процесс сервера принудительно завершен',
        ],
        'backup' => [
            'download' => 'Скачана резервная копия :name',
            'delete' => 'Удалена резервная копия :name',
            'restore' => 'Восстановлена резервная копия :name (удаленные файлы: :truncate)',
            'restore-complete' => 'Завершено восстановление резервной копии :name',
            'restore-failed' => 'Не удалось завершить восстановление резервной копии :name',
            'start' => 'Начато создание новой резервной копии :name',
            'complete' => 'Резервная копия :name помечена как завершенная',
            'fail' => 'Резервная копия :name помечена как неудачная',
            'lock' => 'Резервная копия :name заблокирована',
            'unlock' => 'Резервная копия :name разблокирована',
        ],
        'database' => [
            'create' => 'Создана новая база данных :name',
            'rotate-password' => 'Пароль изменен для базы данных :name',
            'delete' => 'Удалена база данных :name',
        ],
        'file' => [
            'compress_one' => 'Сжат :directory:file',
            'compress_other' => 'Сжато :count файлов в :directory',
            'read' => 'Просмотрено содержимое :file',
            'copy' => 'Создана копия :file',
            'create-directory' => 'Создана директория :directory:name',
            'decompress' => 'Распакованы :files в :directory',
            'delete_one' => 'Удален :directory:files.0',
            'delete_other' => 'Удалено :count файлов в :directory',
            'download' => 'Скачан :file',
            'pull' => 'Скачан удаленный файл из :url в :directory',
            'rename_one' => 'Переименован :directory:files.0.from в :directory:files.0.to',
            'rename_other' => 'Переименовано :count файлов в :directory',
            'write' => 'Записано новое содержимое в :file',
            'upload' => 'Начата загрузка файла',
            'uploaded' => 'Загружен :directory:file',
        ],
        'sftp' => [
            'denied' => 'Доступ SFTP заблокирован из-за прав доступа',
            'create_one' => 'Создан :files.0',
            'create_other' => 'Создано :count новых файлов',
            'write_one' => 'Изменено содержимое :files.0',
            'write_other' => 'Изменено содержимое :count файлов',
            'delete_one' => 'Удален :files.0',
            'delete_other' => 'Удалено :count файлов',
            'create-directory_one' => 'Создана директория :files.0',
            'create-directory_other' => 'Создано :count директорий',
            'rename_one' => 'Переименован :files.0.from в :files.0.to',
            'rename_other' => 'Переименовано или перемещено :count файлов',
        ],
        'allocation' => [
            'create' => 'Добавлено :allocation к серверу',
            'notes' => 'Обновлены заметки для :allocation с ":old" на ":new"',
            'primary' => 'Установлено :allocation как основное выделение сервера',
            'delete' => 'Удалено выделение :allocation',
        ],
        'schedule' => [
            'create' => 'Создано расписание :name',
            'update' => 'Обновлено расписание :name',
            'execute' => 'Вручную выполнено расписание :name',
            'delete' => 'Удалено расписание :name',
        ],
        'task' => [
            'create' => 'Создана новая задача ":action" для расписания :name',
            'update' => 'Обновлена задача ":action" для расписания :name',
            'delete' => 'Удалена задача для расписания :name',
        ],
        'settings' => [
            'rename' => 'Сервер переименован с :old на :new',
            'description' => 'Описание сервера изменено с :old на :new',
        ],
        'startup' => [
            'edit' => 'Переменная :variable изменена с ":old" на ":new"',
            'image' => 'Docker образ для сервера обновлен с :old на :new',
        ],
        'subuser' => [
            'create' => 'Добавлен :email как дополнительный пользователь',
            'update' => 'Обновлены права доступа для :email',
            'delete' => 'Удален :email из дополнительных пользователей',
        ],
    ],
];
