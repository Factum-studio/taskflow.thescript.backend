<?php

declare(strict_types=1);

use modules\rbac\application\port\IAuthorizationService;
use modules\rbac\application\port\IPermissionRepository;
use modules\rbac\application\port\ISubjectResolver;
use modules\rbac\application\port\IRoleAssignmentPolicy;
use modules\rbac\application\service\AuthorizationService;
use modules\rbac\infrastructure\repository\DbPermissionRepository;
use modules\rbac\infrastructure\repository\DbSubjectResolver;
use modules\rbac\infrastructure\repository\DbRoleAssignmentPolicy;
use modules\rbac\presentation\middleware\RbacMiddleware;
use yii\di\Container;

/** @var Container $container */
$params = require __DIR__ . '/params.php';

$container->setSingleton(IPermissionRepository::class, static fn () => new DbPermissionRepository());
$container->setSingleton(ISubjectResolver::class, static fn () => new DbSubjectResolver());
$container->setSingleton(IRoleAssignmentPolicy::class, static fn () => new DbRoleAssignmentPolicy(
    $params['role_ranks'],
));

$container->setSingleton(IAuthorizationService::class, static fn () => new AuthorizationService(
    $container->get(IPermissionRepository::class),
));

$container->setSingleton(RbacMiddleware::class, static fn () => new RbacMiddleware(
    $container->get(IAuthorizationService::class),
    $container->get(ISubjectResolver::class),
    $container->get(IRoleAssignmentPolicy::class),
    $params['public_routes'],
    $params['permission_rules'],
));
