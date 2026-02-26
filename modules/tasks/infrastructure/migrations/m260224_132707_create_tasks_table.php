<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tasks}}`.
 */
class m260224_132707_create_tasks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tasks}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'status_id' => $this->integer()->notNull(),
            'priority_id' => $this->integer()->notNull(),
            'due_date' => $this->timestamp()->null(),
            'created_by' => $this->integer()->notNull(),
            'assigned_to' => $this->integer()->null(),
            'board_id' => $this->integer()->null(),
            'parent_id' => $this->integer()->null(),
            'overdue' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
        ]);

        $this->addForeignKey(
            'fk-tasks-status_id',
            '{{%tasks}}',
            'status_id',
            '{{%task_statuses}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-tasks-priority_id',
            '{{%tasks}}',
            'priority_id',
            '{{%task_priorities}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-tasks-parent_id',
            '{{%tasks}}',
            'parent_id',
            '{{%tasks}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->createIndex('idx-tasks-status_id', '{{%tasks}}', 'status_id');
        $this->createIndex('idx-tasks-assigned_to', '{{%tasks}}', 'assigned_to');
        $this->createIndex('idx-tasks-board_id', '{{%tasks}}', 'board_id');
        $this->createIndex('idx-tasks-due_date', '{{%tasks}}', 'due_date');
        $this->createIndex('idx-tasks-deleted_at', '{{%tasks}}', 'deleted_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-tasks-status_id', '{{%tasks}}');
        $this->dropForeignKey('fk-tasks-priority_id', '{{%tasks}}');
        $this->dropForeignKey('fk-tasks-parent_id', '{{%tasks}}');
        $this->dropTable('{{%tasks}}');
    }
}
