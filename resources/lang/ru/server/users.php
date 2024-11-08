<?php

return [
    'permissions' => [
        'websocket_*' => 'Разрешает доступ к веб-сокету для этого сервера.',
        'control_console' => 'Разрешает пользователю отправлять данные в консоль сервера.',
        'control_start' => 'Разрешает пользователю запускать сервер.',
        'control_stop' => 'Разрешает пользователю останавливать сервер.',
        'control_restart' => 'Разрешает пользователю перезапускать сервер.',
        'control_kill' => 'Разрешает пользователю принудительно завершать процесс сервера.',
        'user_create' => 'Разрешает пользователю создавать новые учетные записи для сервера.',
        'user_read' => 'Разрешает пользователю просматривать пользователей, связанных с этим сервером.',
        'user_update' => 'Разрешает пользователю изменять других пользователей, связанных с этим сервером.',
        'user_delete' => 'Разрешает пользователю удалять других пользователей, связанных с этим сервером.',
        'file_create' => 'Разрешает пользователю создавать новые файлы и директории.',
        'file_read' => 'Разрешает пользователю просматривать файлы и папки, связанные с этим сервером, а также их содержимое.',
        'file_update' => 'Разрешает пользователю обновлять файлы и папки, связанные с сервером.',
        'file_delete' => 'Разрешает пользователю удалять файлы и директории.',
        'file_archive' => 'Разрешает пользователю создавать архивы файлов и распаковывать существующие архивы.',
        'file_sftp' => 'Разрешает пользователю выполнять вышеуказанные действия с файлами через SFTP клиент.',
        'allocation_read' => 'Разрешает доступ к страницам управления выделенными ресурсами сервера.',
        'allocation_update' => 'Разрешает пользователю изменять выделенные ресурсы сервера.',
        'database_create' => 'Разрешает пользователю создавать новую базу данных для сервера.',
        'database_read' => 'Разрешает пользователю просматривать базы данных сервера.',
        'database_update' => 'Разрешает пользователю вносить изменения в базу данных. Если у пользователя нет разрешения "Просмотр пароля", он не сможет изменить пароль.',
        'database_delete' => 'Разрешает пользователю удалять базу данных.',
        'database_view_password' => 'Разрешает пользователю просматривать пароль базы данных в системе.',
        'schedule_create' => 'Разрешает пользователю создавать новое расписание для сервера.',
        'schedule_read' => 'Разрешает пользователю просматривать расписания сервера.',
        'schedule_update' => 'Разрешает пользователю вносить изменения в существующее расписание сервера.',
        'schedule_delete' => 'Разрешает пользователю удалять расписание сервера.',
    ],
];
