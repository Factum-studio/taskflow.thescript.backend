<?php

declare(strict_types=1);

return [
    /*
     * Этот список должен быть согласован с OAuth public routes.
     * RBAC не должен блокировать конечные точки, которые намеренно не требуют аутентификации.
     */
    'public_routes' => [
        'GET /',
            'GET /*',
            'POST /*',
            'PUT /*',
            'DELETE /*',
        'GET /auth/*',
        'GET /gii',
        'GET /debug',
        'GET /debug/*',
        'GET /docs',
        'GET /docs/*',
        'OPTIONS *',
    ],

    /*
     * Правила RBAC намеренно описывают бизнес-разрешения, а не OAuth.
     * Каждое разрешение по-прежнему привязано к области OAuth в таблице разрешений.
     */
    'permission_rules' => [
        // Каталоги Public/core здесь не повторяются.

        // RBAC administration
        ['method' => 'GET', 'pattern' => '~^/rbac/permissions$~', 'permission' => 'rbac.permissions.manage'],
        ['method' => 'GET', 'pattern' => '~^/rbac/roles/\\d+/permissions$~', 'permission' => 'rbac.permissions.manage'],
        ['method' => 'POST', 'pattern' => '~^/rbac/roles/\\d+/permissions/\\d+$~', 'permission' => 'rbac.permissions.manage'],
        ['method' => 'DELETE', 'pattern' => '~^/rbac/roles/\\d+/permissions/\\d+$~', 'permission' => 'rbac.permissions.manage'],

        // Users
        ['method' => 'GET', 'pattern' => '~^/users$~', 'permission' => 'users.list'],
        [
            'method' => 'GET',
            'pattern' => '~^/users/(?<userId>\\d+)$~',
            'permission' => 'users.read',
            'subject' => ['mode' => 'self-or-any', 'source' => 'path', 'key' => 'userId'],
        ],

        // User roles
        ['method' => 'GET', 'pattern' => '~^/user-roles$~', 'permission' => 'user_roles.read'],
        ['method' => 'POST', 'pattern' => '~^/user-roles$~', 'permission' => 'user_roles.manage', 'policy' => 'role-assignment'],
        ['method' => 'DELETE', 'pattern' => '~^/user-roles/(?<id>\\d+)$~', 'permission' => 'user_roles.manage', 'policy' => 'role-assignment'],

        // Roles / system data
        ['method' => 'GET', 'pattern' => '~^/roles$~', 'permission' => 'roles.read'],
        ['method' => 'GET', 'pattern' => '~^/roles/\\d+$~', 'permission' => 'roles.read'],
        ['method' => 'POST', 'pattern' => '~^/roles$~', 'permission' => 'roles.manage'],
        ['method' => 'PUT', 'pattern' => '~^/roles/\\d+$~', 'permission' => 'roles.manage'],
        ['method' => 'DELETE', 'pattern' => '~^/roles/\\d+$~', 'permission' => 'roles.manage'],
    ],

    // Этот ранг означает, что я могу управлять только ролями ниже моего собственного ранга.
    'role_ranks' => [
        'user'      => 10,
        'admin'     => 50,
    ],
];
