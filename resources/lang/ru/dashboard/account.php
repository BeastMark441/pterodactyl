<?php

return [
    'email' => [
        'title' => 'Обновить email',
        'updated' => 'Ваш email адрес был обновлен.',
    ],
    'password' => [
        'title' => 'Изменить пароль',
        'requirements' => 'Новый пароль должен содержать не менее 8 символов.',
        'updated' => 'Ваш пароль был обновлен.',
    ],
    'two_factor' => [
        'button' => 'Настроить двухфакторную аутентификацию',
        'disabled' => 'Двухфакторная аутентификация отключена для вашей учетной записи. При входе в систему больше не потребуется вводить код.',
        'enabled' => 'Двухфакторная аутентификация включена для вашей учетной записи! Теперь при входе в систему вам потребуется вводить код, сгенерированный вашим устройством.',
        'invalid' => 'Предоставленный код недействителен.',
        'setup' => [
            'title' => 'Настройка двухфакторной аутентификации',
            'help' => 'Не можете отсканировать код? Введите код ниже в ваше приложение:',
            'field' => 'Введите код',
        ],
        'disable' => [
            'title' => 'Отключить двухфакторную аутентификацию',
            'field' => 'Введите код',
        ],
    ],
];