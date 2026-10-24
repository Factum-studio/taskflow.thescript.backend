<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\migrations;

use yii\db\Migration;

/**
 * Creates project_roles table.
 *
 * Stores named roles (admin, member, manager, etc.) within a project.
 * Each role has a rank defining the hierarchy.
 */
class m261008_000002_create_project_roles_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%project_roles}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'name' => $this->string(100)->notNull(),
            'slug' => $this->string(50)->notNull(),
            'rank' => $this->smallInteger()->notNull()->defaultValue(10),
            'is_system' => $this->boolean()->notNull()->defaultValue(false),
            'is_preset' => $this->boolean()->notNull()->defaultValue(false),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-project_roles-project_id',
            '{{%project_roles}}',
            'project_id',
            '{{%project}}',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $this->createIndex('idx-project_roles-project_id', '{{%project_roles}}', 'project_id');
        $this->createIndex('idx-project_roles-project_slug', '{{%project_roles}}', ['project_id', 'slug']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey('fk-project_roles-project_id', '{{%project_roles}}');
        $this->dropTable('{{%project_roles}}');
    }
}
