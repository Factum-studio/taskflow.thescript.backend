<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\migrations;

use yii\db\Migration;

/**
 * Alters project_user table:
 *   - adds `role_id` column (FK → project_roles)
 *   - creates default admin/member roles for all existing projects
 *   - migrates existing role (varchar) values to role_id
 *
 * This migration must run AFTER project_roles and project tables exist.
 */
class m261008_000005_alter_project_user_add_role_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        // 1. Add nullable role_id column
        $this->addColumn('{{%project_user}}', 'role_id', 'INT NULL');

        // 2. Create default admin role for every project that does not have one yet
        $this->execute("
            INSERT INTO {{%project_roles}}
                (project_id, name, slug, rank, is_system, is_preset, created_at, updated_at)
            SELECT
                p.id, 'Admin', 'admin', 100, 1, 0, NOW(), NOW()
            FROM {{%project}} p
            WHERE NOT EXISTS (
                SELECT 1 FROM {{%project_roles}} pr
                WHERE pr.project_id = p.id AND pr.slug = 'admin'
            )
        ");

        // 3. Create default member role for every project that does not have one yet
        $this->execute("
            INSERT INTO {{%project_roles}}
                (project_id, name, slug, rank, is_system, is_preset, created_at, updated_at)
            SELECT
                p.id, 'Member', 'member', 10, 1, 0, NOW(), NOW()
            FROM {{%project}} p
            WHERE NOT EXISTS (
                SELECT 1 FROM {{%project_roles}} pr
                WHERE pr.project_id = p.id AND pr.slug = 'member'
            )
        ");

        // 4. Migrate existing role (varchar) → role_id (FK) by matching slug
        $this->execute("
            UPDATE {{%project_user}} pu
            JOIN {{%project_roles}} pr
                ON pr.project_id = pu.project_id
                AND pr.slug = pu.role
            SET pu.role_id = pr.id
            WHERE pu.role_id IS NULL
        ");

        // 5. Fallback: any leftover NULL role_id → assign to 'member' role
        $this->execute("
            UPDATE {{%project_user}} pu
            JOIN {{%project_roles}} pr
                ON pr.project_id = pu.project_id
                AND pr.slug = 'member'
            SET pu.role_id = pr.id
            WHERE pu.role_id IS NULL
        ");

        // 5a. The project owner must always have the project-local admin role,
        // regardless of the legacy role string stored before local RBAC.
        $this->execute("
            UPDATE {{%project_user}} pu
            JOIN {{%project}} p ON p.id = pu.project_id AND p.owner_id = pu.user_id
            JOIN {{%project_roles}} pr ON pr.project_id = pu.project_id AND pr.slug = 'admin'
            SET pu.role_id = pr.id
        ");

        // 6. Add foreign key constraint
        $this->addForeignKey(
            'fk-project_user-role_id',
            '{{%project_user}}',
            'role_id',
            '{{%project_roles}}',
            'id',
            'RESTRICT',
            'CASCADE',
        );

        // 7. Index on role_id for faster lookups
        $this->createIndex('idx-project_user-role_id', '{{%project_user}}', 'role_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey('fk-project_user-role_id', '{{%project_user}}');
        $this->dropIndex('idx-project_user-role_id', '{{%project_user}}');
        $this->dropColumn('{{%project_user}}', 'role_id');
    }
}
