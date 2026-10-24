<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\service;

use modules\projectRbac\application\port\IBillingLimitsService;

/**
 * Stub implementation of IBillingLimitsService.
 *
 * Allows everything (no limits), to be replaced with real billing integration.
 */
class BillingLimitsServiceStub implements IBillingLimitsService
{
    public function isFeatureAvailable(int $userId, string $permissionCode): bool
    {
        // Default stub: all features are available
        return true;
    }

    public function getAvailableFeatures(int $userId): array
    {
        // Return wildcard — all features
        return ['*'];
    }

    public function getMaxProjects(int $userId): int
    {
        return 9999;
    }

    public function getMaxMembers(int $projectId): int
    {
        return 9999;
    }

    public function getMaxBoards(int $projectId): int
    {
        return 9999;
    }

    public function getMaxColumns(int $projectId): int
    {
        return 9999;
    }

    public function canCreateProject(int $userId): bool
    {
        return true;
    }

    public function canAddMember(int $projectId): bool
    {
        return true;
    }

    public function canCreateBoard(int $projectId): bool
    {
        return true;
    }

    public function canCreateColumn(int $projectId): bool
    {
        return true;
    }
}
