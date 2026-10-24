<?php

declare(strict_types=1);

namespace modules\projectRbac\application\port;

/**
 * Interface for billing limits and feature segmentation.
 *
 * Implemented as a stub initially (reads from config), later
 * replaced with a real billing module integration.
 */
interface IBillingLimitsService
{
    // ── Feature availability (functional segmentation) ──────

    /**
     * Is a specific permission/feature available on the user's tariff plan?
     *
     * @param int $userId
     * @param string $permissionCode
     * @return bool
     */
    public function isFeatureAvailable(int $userId, string $permissionCode): bool;

    /**
     * Returns the list of all feature codes available for the user's tariff.
     *
     * @param int $userId
     * @return string[]
     */
    public function getAvailableFeatures(int $userId): array;

    // ── Quota / limits ──────────────────────────────────────

    /**
     * @param int $userId
     * @return int max projects the user can own
     */
    public function getMaxProjects(int $userId): int;

    /**
     * @param int $projectId
     * @return int max members allowed in the project
     */
    public function getMaxMembers(int $projectId): int;

    /**
     * @param int $projectId
     * @return int max boards allowed in the project
     */
    public function getMaxBoards(int $projectId): int;

    /**
     * @param int $projectId
     * @return int max columns (statuses) allowed in the project
     */
    public function getMaxColumns(int $projectId): int;

    // ── Convenience checks ──────────────────────────────────

    /**
     * @param int $userId
     * @return bool
     */
    public function canCreateProject(int $userId): bool;

    /**
     * @param int $projectId
     * @return bool
     */
    public function canAddMember(int $projectId): bool;

    /**
     * @param int $projectId
     * @return bool
     */
    public function canCreateBoard(int $projectId): bool;

    /**
     * @param int $projectId
     * @return bool
     */
    public function canCreateColumn(int $projectId): bool;
}
