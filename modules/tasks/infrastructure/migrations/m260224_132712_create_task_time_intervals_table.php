<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_time_intervals}}`.
 */
class m260224_132712_create_task_time_intervals_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_time_intervals}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'start_time' => $this->timestamp()->notNull(),
            'end_time' => $this->timestamp()->null(),
            'duration' => $this->integer()->null(), // секунды
            'comment' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-task_time_intervals-task_id',
            '{{%task_time_intervals}}',
            'task_id',
            '{{%tasks}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-task_time_intervals-task_id', '{{%task_time_intervals}}', 'task_id');
        $this->createIndex('idx-task_time_intervals-user_id', '{{%task_time_intervals}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-task_time_intervals-task_id', '{{%task_time_intervals}}');
        $this->dropTable('{{%task_time_intervals}}');
    }
}
