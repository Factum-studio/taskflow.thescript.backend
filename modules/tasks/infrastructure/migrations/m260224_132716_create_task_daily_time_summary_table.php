<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_daily_time_summary}}`.
 */
class m260224_132716_create_task_daily_time_summary_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_daily_time_summary}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'total_duration' => $this->integer()->notNull()->defaultValue(0),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-task_daily_time_summary-task_id',
            '{{%task_daily_time_summary}}',
            'task_id',
            '{{%tasks}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-task_daily_time_summary-task_user_date', '{{%task_daily_time_summary}}', ['task_id', 'user_id', 'date'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-task_daily_time_summary-task_id', '{{%task_daily_time_summary}}');
        $this->dropTable('{{%task_daily_time_summary}}');
    }
}
