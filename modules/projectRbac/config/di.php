<?php

declare(strict_types=1);

use modules\projectRbac\application\port\ILocalRbacService;
use modules\projectRbac\application\port\IProjectRoleRepository;
use modules\projectRbac\application\port\IBillingLimitsService;
use modules\projectRbac\application\service\LocalRbacService;
use modules\projectRbac\application\service\ProjectRoleService;
use modules\projectRbac\application\service\ProjectMembershipService;
use modules\projectRbac\infrastructure\repository\DbProjectRoleRepository;
use modules\projectRbac\infrastructure\service\ConfigBillingLimitsService;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\repository\IBoardRepository;
use modules\tasks\domain\repository\IBoardColumnRepository;
use yii\di\Container;

/** @var Container $container */

// ── Repository ──────────────────────────────────────────────
$container->set(IProjectRoleRepository::class, function () {
    return new DbProjectRoleRepository(Yii::$app->db);
});

// ── Billing (config-driven, replace with real billing module later) ─
$params = require __DIR__ . '/params.php';
$container->set(IBillingLimitsService::class, function (Container $c) use ($params) {
    return new ConfigBillingLimitsService(
        $c->get(IProjectRepository::class),
        $c->get(IBoardRepository::class),
        $c->get(IBoardColumnRepository::class),
        $c->get(IProjectUserRepository::class),
        $params,
    );
});

// ── Services ────────────────────────────────────────────────
$container->set(ILocalRbacService::class, function (Container $c) {
    return new LocalRbacService(
        $c->get(IProjectRoleRepository::class),
        $c->get(IProjectRepository::class),
        $c->get(IProjectUserRepository::class),
        $c->get(IBillingLimitsService::class),
    );
});

$container->set(ProjectRoleService::class, function (Container $c) {
    return new ProjectRoleService(
        $c->get(IProjectRoleRepository::class),
        $c->get(IProjectUserRepository::class),
    );
});

$container->set(ProjectMembershipService::class, function (Container $c) {
    return new ProjectMembershipService(
        $c->get(IProjectRepository::class),
        $c->get(IProjectUserRepository::class),
        $c->get(IProjectRoleRepository::class),
        Yii::$app->db,
    );
});
