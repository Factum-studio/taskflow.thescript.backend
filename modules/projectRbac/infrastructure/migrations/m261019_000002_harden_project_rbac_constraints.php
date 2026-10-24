<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\migrations;

use yii\db\Migration;

/** Enforce per-project slug uniqueness and ensure the project.view permission exists on upgraded installations. */
class m261019_000002_harden_project_rbac_constraints extends Migration
{
    public function safeUp(): void
    {
        $this->execute("
            INSERT IGNORE INTO {{%project_permission_codes}} (code, `group`, description)
            VALUES ('project.view', 'project', 'Просмотр проекта')
        
        " );

        $duplicates = $this->db->createCommand("
            SELECT project_id, slug, GROUP_CONCAT(id ORDER BY id ASC) AS ids
            FROM {{%project_roles}}
            GROUP BY project_id, slug
            HAVING COUNT(*) > 1
        
        " )->queryAll();
        foreach ($duplicates as $duplicate) {
            $ids = array_map('intval', explode(',', (string)$duplicate['ids']));
            array_shift($ids); // preserve the earliest role's slug
            foreach ($ids as $id) {
                $this->update('{{%project_roles}}', ['slug' => substr((string)$duplicate['slug'], 0, 40) . '-' . $id], ['id' => $id]);
            }
        }

        $this->createIndex('uq-project_roles-project_slug', '{{%project_roles}}', ['project_id', 'slug'], true);
        $this->dropIndex('idx-project_roles-project_slug', '{{%project_roles}}');

        $this->execute("
            INSERT IGNORE INTO {{%project_role_permissions}} (role_id, permission_code)
            SELECT pr.id, 'project.view' FROM {{%project_roles}} pr
            WHERE (pr.slug IN ('admin', 'member') AND pr.is_system = 1)
               OR (pr.slug = 'manager' AND pr.is_preset = 1)
        ");

        $this->execute("
            UPDATE {{%project_user}} pu
            JOIN {{%project}} p ON p.id = pu.project_id AND p.owner_id = pu.user_id
            JOIN {{%project_roles}} pr ON pr.project_id = pu.project_id AND pr.slug = 'admin'
            SET pu.role_id = pr.id
        ");

        $this->execute("DELETE pup FROM {{%project_user_permissions}} pup LEFT JOIN {{%project_user}} pu ON pu.project_id = pup.project_id AND pu.user_id = pup.user_id WHERE pu.project_id IS NULL");
        $membershipFkExists = (int)$this->db->createCommand("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'project_user_permissions' AND CONSTRAINT_NAME = 'fk-user_perm-project_user'")->queryScalar() > 0;
        if (!$membershipFkExists) {
            $this->addForeignKey('fk-user_perm-project_user', '{{%project_user_permissions}}', ['project_id', 'user_id'], '{{%project_user}}', ['project_id', 'user_id'], 'CASCADE', 'CASCADE');
        }
    }

    public function safeDown(): void
    {
        $membershipFkExists = (int)$this->db->createCommand("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'project_user_permissions' AND CONSTRAINT_NAME = 'fk-user_perm-project_user'")->queryScalar() > 0;
        if ($membershipFkExists) {
            $this->dropForeignKey('fk-user_perm-project_user', '{{%project_user_permissions}}');
        }
        $this->dropIndex('uq-project_roles-project_slug', '{{%project_roles}}');
        $this->createIndex('idx-project_roles-project_slug', '{{%project_roles}}', ['project_id', 'slug']);
        $this->delete('{{%project_permission_codes}}', ['code' => 'project.view']);
    }
}
