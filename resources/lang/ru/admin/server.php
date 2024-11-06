<?php

return [
    'exceptions' => [
        'no_new_default_allocation' => 'Вы пытаетесь удалить выделение по умолчанию для этого сервера, но нет резервного выделения для использования.',
        'marked_as_failed' => 'Этот сервер был помечен как неудачный при предыдущей установке. Текущий статус не может быть изменен в этом состоянии.',
        'bad_variable' => 'Произошла ошибка валидации с переменной :name.',
        'daemon_exception' => 'При попытке связаться с демоном произошла ошибка, что привело к ответу HTTP/:code. Эта ошибка была записана в журнал. (ID запроса: :request_id)',
        'default_allocation_not_found' => 'Запрошенное выделение по умолчанию не найдено в выделениях этого сервера.',
    ],
    'alerts' => [
        'startup_changed' => 'Конфигурация запуска для этого сервера обновлена. Если гнездо или яйцо этого сервера были изменены, сейчас выполняется переустановка.',
        'server_deleted' => 'Сервер успешно удален из системы.',
        'server_created' => 'Сервер успешно создан на панели. Пожалуйста, дайте демону несколько минут для полной установки этого сервера.',
        'build_updated' => 'Параметры сборки для этого сервера обновлены. Некоторые изменения могут потребовать перезапуска для вступления в силу.',
        'suspension_toggled' => 'Статус приостановки сервера изменен на :status.',
        'rebuild_on_boot' => 'Этот сервер помечен как требующий пересборки Docker контейнера. Это произойдет при следующем запуске сервера.',
        'install_toggled' => 'Статус установки для этого сервера изменен.',
        'server_reinstalled' => 'Этот сервер поставлен в очередь на переустановку, начиная прямо сейчас.',
        'details_updated' => 'Детали сервера успешно обновлены.',
        'docker_image_updated' => 'Успешно изменен Docker образ по умолчанию для этого сервера. Требуется перезагрузка для применения этого изменения.',
        'node_required' => 'Вы должны иметь хотя бы один узел, прежде чем добавлять сервер в эту панель.',
        'transfer_nodes_required' => 'Вы должны иметь хотя бы два узла, прежде чем переносить серверы.',
        'transfer_started' => 'Перенос сервера начат.',
        'transfer_not_viable' => 'Выбранный узел не имеет необходимого дискового пространства или памяти для размещения этого сервера.',
    ],
];