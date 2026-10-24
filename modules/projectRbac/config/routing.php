<?php

declare(strict_types=1);

return [
    // ── Roles ──────────────────────────────────────────────
    'GET    project/<projectId:\d+>/role'                                     => 'project-rbac/project-rbac/index',
    'POST   project/<projectId:\d+>/role'                                     => 'project-rbac/project-rbac/create',
    'PUT    project/<projectId:\d+>/role/<roleId:\d+>'                        => 'project-rbac/project-rbac/update',
    'DELETE project/<projectId:\d+>/role/<roleId:\d+>'                        => 'project-rbac/project-rbac/delete',

    // ── Role Permissions ───────────────────────────────────
    'GET    project/<projectId:\d+>/role/<roleId:\d+>/permission'             => 'project-rbac/project-rbac/role-permissions',
    'PUT    project/<projectId:\d+>/role/<roleId:\d+>/permission'             => 'project-rbac/project-rbac/update-role-permissions',

    // ── User Overrides ─────────────────────────────────────
    'GET    project/<projectId:\d+>/member/<memberId:\d+>/permission'                   => 'project-rbac/project-rbac/member-permissions',
    'PUT    project/<projectId:\d+>/member/<memberId:\d+>/permission'                   => 'project-rbac/project-rbac/update-member-permission',
    'DELETE project/<projectId:\d+>/member/<memberId:\d+>/permission/<permissionCode:.*>' => 'project-rbac/project-rbac/delete-member-permission',

    // ── Computed Matrix ────────────────────────────────────
    'GET    project/<projectId:\d+>/member/<memberId:\d+>/effective-permissions' => 'project-rbac/project-rbac/effective-permissions',

    // ── Leave & Transfer ──────────────────────────────────
    'POST   project/<projectId:\d+>/leave'                                    => 'project-rbac/project-rbac/leave',
    'POST   project/<projectId:\d+>/transfer'                                 => 'project-rbac/project-rbac/transfer',
];