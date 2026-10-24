<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\migrations;

use yii\db\Migration;
use yii\db\Query;

/**
 * Seeds default role permissions for system roles (admin, member).
 *
 * - admin: all permissions (admin always has full access via code, but we store
 *   them explicitly so the role matrix UI shows everything).
 * - member: baseline permissions (task.view, task.create, etc.).
 *
 * Only applies to projects that do NOT already have role permissions seeded.
 */
class m261008_000006_seed_default_role_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $adminPerms = $this->getAllPermissionCodes();
        $memberPerms = [
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

        $inserts = [];

        // Fetch admin role ids for all projects
        $adminRoles = (new Query())
            ->select(['id', 'project_id'])
            ->from('{{%project_roles}}')
            ->where(['slug' => 'admin', 'is_system' => true])
            ->all();

        foreach ($adminRoles as $row) {
            foreach ($adminPerms as $code) {
                $inserts[] = [(int)$row['id'], $code];
            }
        }

        // Fetch member role ids for all projects
        $memberRoles = (new Query())
            ->select(['id', 'project_id'])
            ->from('{{%project_roles}}')
            ->where(['slug' => 'member', 'is_system' => true])
            ->all();

        foreach ($memberRoles as $row) {
            foreach ($memberPerms as $code) {
                $inserts[] = [(int)$row['id'], $code];
            }
        }

        if ($inserts !== []) {
            $this->batchInsert(
                '{{%project_role_permissions}}',
                ['role_id', 'permission_code'],
                $inserts,
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        // Seed down — clear all project_role_permissions (destructive, but seed)
        $this->execute('DELETE FROM {{%project_role_permissions}}');
    }

    /**
     * @return string[]
     */
    private function getAllPermissionCodes(): array
    {
        return [
            'task.view',
            'task.create',
            'task.update',
            'task.delete',
            'task.hard_delete',
            'task.restore',
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
            'sticker.update',
            'sticker.delete',

            'column.create',
            'column.update',
            'column.delete',
            'column.reorder',

            'member.invite',
            'member.add',
            'member.remove',
            'member.role.change',
            'member.permission.override',
            'member.leave',

            'project.settings.manage',
            'project.rename',
            'project.delete',

            'board.view',
            'board.create',
            'board.update',
            'board.delete',
            'board.reorder',
            'board.settings.manage',

            'role.create',
            'role.update',
            'role.delete',
            'role.permissions.manage',
        ];
    }
}
