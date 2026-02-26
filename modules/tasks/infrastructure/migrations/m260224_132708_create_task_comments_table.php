<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_comments}}`.
 */
class m260224_132708_create_task_comments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_comments}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'content' => $this->text()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-task_comments-task_id',
            '{{%task_comments}}',
            'task_id',
            '{{%tasks}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-task_comments-task_id', '{{%task_comments}}', 'task_id');
        $this->createIndex('idx-task_comments-user_id', '{{%task_comments}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-task_comments-task_id', '{{%task_comments}}');
        $this->dropTable('{{%task_comments}}');
    }
}
