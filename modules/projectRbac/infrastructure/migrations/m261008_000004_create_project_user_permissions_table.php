<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\migrations;

use yii\db\Migration;

/**
 * Creates project_user_permissions table.
 *
 * Individual permission overrides (allow / deny) per project member.
 * These override the base role permissions for a specific user.
 */
class m261008_000004_create_project_user_permissions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%project_user_permissions}}', [
            'project_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'permission_code' => $this->string(100)->notNull(),
            'is_granted' => $this->boolean()->notNull()->defaultValue(true),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'PRIMARY KEY(project_id, user_id, permission_code)',
        ]);

        $this->addForeignKey(
            'fk-user_perm-permission_code',
            '{{%project_user_permissions}}',
            'permission_code',
            '{{%project_permission_codes}}',
            'code',
            'CASCADE',
            'CASCADE',
        );

        $this->createIndex(
            'idx-user_perm-project_user',
            '{{%project_user_permissions}}',
            ['project_id', 'user_id'],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey('fk-user_perm-permission_code', '{{%project_user_permissions}}');
        $this->dropTable('{{%project_user_permissions}}');
    }
}
