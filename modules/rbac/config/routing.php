<?php

declare(strict_types=1);

return [
    'GET rbac/permissions'                                          => 'rbac/permission/index',
    'GET rbac/roles/<roleId:\d+>/permissions'                       => 'rbac/role-permission/index',
    'POST rbac/roles/<roleId:\d+>/permissions/<permissionId:\d+>'   => 'rbac/role-permission/grant',
    'DELETE rbac/roles/<roleId:\d+>/permissions/<permissionId:\d+>' => 'rbac/role-permission/revoke',
];
