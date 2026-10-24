<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\repository;

use modules\projectRbac\application\port\IProjectRoleRepository;
use modules\projectRbac\domain\valueObject\ProjectRole as ProjectRoleVO;
use modules\projectRbac\domain\valueObject\ProjectRoleRank;
use modules\projectRbac\infrastructure\persistence\ProjectRoleAR;
use modules\projectRbac\infrastructure\persistence\ProjectUserPermissionAR;
use DateTimeImmutable;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use yii\db\Exception as DbException;
use yii\db\Query;
use yii\db\StaleObjectException;

class DbProjectRoleRepository implements IProjectRoleRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    // ── Role CRUD ────────────────────────────────────────────

    public function findRoleById(int $roleId): ?ProjectRoleVO
    {
        $row = (new Query())
            ->select('*')
            ->from('{{%project_roles}}')
            ->where(['id' => $roleId])
            ->one();

        return $row ? $this->mapRowToVO($row) : null;
    }

    public function findRoleByIdInProject(int $projectId, int $roleId): ?ProjectRoleVO
    {
        $row = (new Query())
            ->select('*')
            ->from('{{%project_roles}}')
            ->where(['id' => $roleId, 'project_id' => $projectId])
            ->one();

        return $row ? $this->mapRowToVO($row) : null;
    }

    public function findRolesByProject(int $projectId): array
    {
        $rows = (new Query())
            ->select('*')
            ->from('{{%project_roles}}')
            ->where(['project_id' => $projectId])
            ->orderBy('rank DESC')
            ->all();

        return array_map([$this, 'mapRowToVO'], $rows);
    }

    public function findSystemRole(int $projectId, string $slug): ?ProjectRoleVO
    {
        $row = (new Query())
            ->select('*')
            ->from('{{%project_roles}}')
            ->where(['project_id' => $projectId, 'slug' => $slug, 'is_system' => true])
            ->one();

        return $row ? $this->mapRowToVO($row) : null;
    }

    /**
     * @throws DbException
     */
    public function insertRole(int $projectId, string $name, string $slug, int $rank, bool $isSystem, bool $isPreset, int $actorUserId): int
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $ar = new ProjectRoleAR();
        $ar->project_id  = $projectId;
        $ar->name        = $name;
        $ar->slug        = $slug;
        $ar->rank        = $rank;
        $ar->is_system   = $isSystem;
        $ar->is_preset   = $isPreset;
        $ar->created_by  = $actorUserId;
        $ar->updated_by  = $actorUserId;
        $ar->created_at  = $now;
        $ar->updated_at  = $now;

        if (!$ar->save()) {
            throw new RuntimeException('Failed to create role: ' . $name);
        }

        return (int)$ar->id;
    }

    /**
     * @throws DbException
     */
    public function updateRole(int $roleId, string $name, int $rank, int $actorUserId): void
    {
        $ar = ProjectRoleAR::findOne($roleId);
        if (!$ar) {
            throw new RuntimeException('Role not found');
        }

        $ar->name       = $name;
        $ar->rank       = $rank;
        $ar->updated_by = $actorUserId;
        $ar->updated_at = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        if (!$ar->save()) {
            throw new RuntimeException('Failed to update role');
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteRole(int $roleId): void
    {
        $ar = ProjectRoleAR::findOne($roleId);
        if (!$ar) {
            throw new RuntimeException('Role not found');
        }
        if ($ar->is_system) {
            throw new RuntimeException('Cannot delete a system role');
        }
        $hasMembers = (new Query())->from('{{%project_user}}')->where(['role_id' => $roleId])->exists();
        if ($hasMembers) {
            throw new RuntimeException('Cannot delete a role assigned to project members');
        }
        $ar->delete();
    }

    // ── Permissions assigned to roles ────────────────────────

    public function findAllPermissionCodes(): array
    {
        $rows = (new Query())
            ->select('code')
            ->from('{{%project_permission_codes}}')
            ->orderBy('code ASC')
            ->all();
        return array_map(static fn (array $row): string => (string)$row['code'], $rows);
    }

    public function findRolePermissionCodes(int $roleId): array
    {
        $rows = (new Query())
            ->select('permission_code')
            ->from('{{%project_role_permissions}}')
            ->where(['role_id' => $roleId])
            ->all();

        return array_map(fn ($r) => $r['permission_code'], $rows);
    }

    /**
     * @throws Throwable
     */
    public function setRolePermissions(int $roleId, array $permissionCodes, int $actorUserId): void
    {
        $this->db->transaction(function (Connection $db) use ($roleId, $actorUserId, $permissionCodes) {
            // Remove existing
            $db->createCommand()->delete(
                '{{%project_role_permissions}}',
                ['role_id' => $roleId],
            )->execute();

            // Insert new
            $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            foreach ($permissionCodes as $code) {
                $db->createCommand()->insert(
                    '{{%project_role_permissions}}',
                    [
                        'role_id' => $roleId,
                        'permission_code' => $code,
                        'created_by' => $actorUserId,
                        'updated_by' => $actorUserId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                )->execute();
            }
        });
    }

    // ── Individual user overrides ────────────────────────────

    public function findUserOverride(int $projectId, int $userId, string $permissionCode): ?bool
    {
        $row = (new Query())
            ->select('is_granted')
            ->from('{{%project_user_permissions}}')
            ->where([
                'project_id' => $projectId,
                'user_id' => $userId,
                'permission_code' => $permissionCode,
            ])
            ->one();

        return $row ? (bool)$row['is_granted'] : null;
    }

    public function findUserOverrides(int $projectId, int $userId): array
    {
        $rows = (new Query())
            ->select(['permission_code', 'is_granted'])
            ->from('{{%project_user_permissions}}')
            ->where(['project_id' => $projectId, 'user_id' => $userId])
            ->all();

        return array_map(fn ($r) => [
            'permission_code' => $r['permission_code'],
            'is_granted' => (bool)$r['is_granted'],
        ], $rows);
    }

    /**
     * @throws DbException
     */
    public function setUserOverride(int $projectId, int $userId, string $permissionCode, bool $isGranted, int $actorUserId): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->db->createCommand(
            'INSERT INTO {{%project_user_permissions}} '
            . '(project_id, user_id, permission_code, is_granted, created_by, updated_by, created_at, updated_at) '
            . 'VALUES (:projectId, :userId, :permissionCode, :isGranted, :createdBy, :updatedBy, :createdAt, :updatedAt) '
            . 'ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_by = VALUES(updated_by), updated_at = VALUES(updated_at)',
            [
                ':projectId' => $projectId,
                ':userId' => $userId,
                ':permissionCode' => $permissionCode,
                ':isGranted' => $isGranted,
                ':createdBy' => $actorUserId,
                ':updatedBy' => $actorUserId,
                ':createdAt' => $now,
                ':updatedAt' => $now,
            ],
        )->execute();
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function removeUserOverride(int $projectId, int $userId, string $permissionCode): void
    {
        $ar = ProjectUserPermissionAR::findOne([
            'project_id' => $projectId,
            'user_id' => $userId,
            'permission_code' => $permissionCode,
        ]);
        $ar?->delete();
    }

    // ── Role seeding for new projects ────────────────────────

    /**
     * @throws DbException
     */
    public function createDefaultRoles(int $projectId, string $projectType, int $ownerId): array
    {
        $existingBySlug = [];
        foreach ($this->findRolesByProject($projectId) as $existingRole) {
            $existingBySlug[$existingRole->getSlug()] = $existingRole;
        }

        if (isset($existingBySlug['admin'])) {
            if (!$existingBySlug['admin']->isSystem()) {
                throw new RuntimeException('The reserved admin role must be a system role');
            }
            $adminId = $existingBySlug['admin']->getId();
        } else {
            $adminId = $this->insertRole($projectId, 'Admin', 'admin', ProjectRoleRank::ADMIN_RANK, true, false, $ownerId);
        }

        if (isset($existingBySlug['member'])) {
            if (!$existingBySlug['member']->isSystem()) {
                throw new RuntimeException('The reserved member role must be a system role');
            }
            $memberId = $existingBySlug['member']->getId();
        } else {
            $memberId = $this->insertRole($projectId, 'Member', 'member', ProjectRoleRank::MEMBER_RANK, true, false, $ownerId);
        }

        $managerId = null;
        if ($projectType === 'corporate') {
            if (isset($existingBySlug['manager'])) {
                if (!$existingBySlug['manager']->isPreset() || $existingBySlug['manager']->isSystem()) {
                    throw new RuntimeException('The reserved manager role must be a preset role');
                }
                $managerId = $existingBySlug['manager']->getId();
            } else {
                $managerId = $this->insertRole($projectId, 'Manager', 'manager', ProjectRoleRank::MANAGER_RANK, false, true, $ownerId);
            }
        }

        // Seed permissions only when a role has no permissions yet.
        $this->seedPermissionsForNewRoles($projectId, $adminId, $memberId, $managerId, $ownerId);

        return [$adminId, $memberId, $managerId];
    }

    // ── Private helpers ──────────────────────────────────────

    /**
     * @throws DbException
     */
    private function seedPermissionsForNewRoles(int $projectId, int $adminId, int $memberId, ?int $managerId, int $actorId): void
    {
        $allPermissions = $this->getAllPermissionCodes();
        $memberPermissions = $this->getMemberDefaultPermissions();
        $managerPermissions = $this->getManagerDefaultPermissions();

        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $inserts = [];

        // Admin: all permissions (only seed an empty role).
        if ($this->findRolePermissionCodes($adminId) === []) {
            foreach ($allPermissions as $code) {
                $inserts[] = [$adminId, $code, $actorId, $actorId, $now, $now];
            }
        }

        // Member: baseline.
        if ($this->findRolePermissionCodes($memberId) === []) {
            foreach ($memberPermissions as $code) {
                $inserts[] = [$memberId, $code, $actorId, $actorId, $now, $now];
            }
        }

        // Manager (corporate only).
        if ($managerId !== null && $this->findRolePermissionCodes($managerId) === []) {
            foreach ($managerPermissions as $code) {
                $inserts[] = [$managerId, $code, $actorId, $actorId, $now, $now];
            }
        }

        if ($inserts !== []) {
            $this->db->createCommand()->batchInsert(
                '{{%project_role_permissions}}',
                ['role_id', 'permission_code', 'created_by', 'updated_by', 'created_at', 'updated_at'],
                $inserts,
            )->execute();
        }
    }

    private function mapRowToVO(array $row): ProjectRoleVO
    {
        return new ProjectRoleVO(
            (int)$row['id'],
            (int)$row['project_id'],
            (string)$row['name'],
            (string)$row['slug'],
            new ProjectRoleRank((int)$row['rank']),
            (bool)$row['is_system'],
            (bool)$row['is_preset'],
        );
    }

    private function getAllPermissionCodes(): array
    {
        $rows = (new Query())
            ->select('code')
            ->from('{{%project_permission_codes}}')
            ->all();
        return array_map(fn ($r) => $r['code'], $rows);
    }

    private function getMemberDefaultPermissions(): array
    {
        return [
            'project.view',
            'task.view',
            'task.create',
            'task.update',
            'task.delete',
            'task.move',
            'task.assign',
            'task.priority.set',
            'task.sticker.attach',
            'task.sticker.detach',
            'comment.create',
            'comment.update',
            'comment.delete.self',
            'board.view',
            'member.leave',
        ];
    }

    private function getManagerDefaultPermissions(): array
    {
        return [
            'project.view',
            'task.view',
            'task.create',
            'task.update',
            'task.delete',
            'task.move',
            'task.assign',
            'task.priority.set',
            'task.sticker.attach',
            'task.sticker.detach',
            'comment.create',
            'comment.update',
            'comment.delete.self',
            'comment.delete.any',
            'sticker.create',
            'column.create',
            'column.update',
            'column.reorder',
            'board.view',
            'board.create',
            'board.update',
            'member.invite',
            'member.add',
            'member.leave',
            'project.settings.manage',
            'project.rename',
        ];
    }
}
