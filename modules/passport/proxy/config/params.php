<?php

declare(strict_types=1);

return [
    'passport_url' => rtrim((string)($_ENV['PASSPORT_URL'] ?? 'https://passport.thescript.agency'), '/'),
    'access_token_cookie_name' => 'taskflow_access_token',

    /**
     * Разрешённая матрица проксирования запросов на центральный сервис (Passport).
     *
     * Ключ — HTTP-метод, значение — массив разрешённых путей.
     * Пути поддерживают wildcard «*» в конце и параметры в виде <param>.
     */
    'proxy' => [
        'GET' => [
            // Должности
            '/posts',
            '/posts/{id}',
            // Роли центрального сервера
            '/roles',
            '/roles/{id}',
            // Пользовательские контакты
            '/user-contacts',
            // Пользователськие образования
            '/user-educations',
            // Роли пользователя
            '/user-roles',
            // Пользователь
            '/users/{id}',
            '/users',
            // Получить свои согласия
            '/users/{id}/accepts',
        ],
        'POST' => [
            // Пользовательские контакты
            '/user-contacts/{id}',
            // Пользователськие образования
            '/user-educations/{id}',
        ],
        'PUT' => [
            // Пользовательские контакты
            '/user-contacts/{id}',
            // Пользователськие образования
            '/user-educations/{id}',
        ],
        'DELETE' => [
            // Пользовательские контакты
            '/user-contacts/{id}',
            // Пользователськие образования
            '/user-educations/{id}',
        ],
    ],
];
