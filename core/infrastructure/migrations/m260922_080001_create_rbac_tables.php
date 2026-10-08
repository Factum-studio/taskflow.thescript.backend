<?php

declare(strict_types=1);

namespace core\infrastructure\migrations;

use yii\db\Migration;

class m260922_080001_create_rbac_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('roles', [
            'id'            => $this->bigPrimaryKey(),
            'name'          => $this->string(50)->notNull()->unique(),
            'description'   => $this->string(255)->null(),
            'created_at'    => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createTable('user_roles', [
            'id'            => $this->bigPrimaryKey(),
            'user_id'       => $this->bigInteger()->notNull(),
            'role_id'       => $this->bigInteger()->notNull(),
            'assigned_at'   => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'assigned_by'   => $this->bigInteger()->null(),
        ]);

        $this->addForeignKey('fk_user_roles_user_id', 'user_roles', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_user_roles_role_id', 'user_roles', 'role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('user_roles');
        $this->dropTable('roles');
    }
}
