<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\migrations;

use yii\db\Migration;

/**
 * Creates project_role_permissions table.
 *
 * Many-to-many relation between project roles and permission codes.
 */
class m261008_000003_create_project_role_permissions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%project_role_permissions}}', [
            'role_id' => $this->integer()->notNull(),
            'permission_code' => $this->string(100)->notNull(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'PRIMARY KEY(role_id, permission_code)',
        ]);

        $this->addForeignKey(
            'fk-role_perm-role_id',
            '{{%project_role_permissions}}',
            'role_id',
            '{{%project_roles}}',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $this->addForeignKey(
            'fk-role_perm-permission_code',
            '{{%project_role_permissions}}',
            'permission_code',
            '{{%project_permission_codes}}',
            'code',
            'CASCADE',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey('fk-role_perm-permission_code', '{{%project_role_permissions}}');
        $this->dropForeignKey('fk-role_perm-role_id', '{{%project_role_permissions}}');
        $this->dropTable('{{%project_role_permissions}}');
    }
}
