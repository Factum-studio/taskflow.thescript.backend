<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_statuses}}`.
 */
class m260224_132704_create_task_statuses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_statuses}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->unique(),
            'label' => $this->string(255)->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
//            'workflow_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // CHECK THIS
//        $this->addForeignKey(
//            'fk-task_statuses-workflow_id',
//            '{{%task_statuses}}',
//            'workflow_id',
//            '{{%workflows}}',
//            'id',
//            'SET NULL',
//            'CASCADE'
//        );

        $statuses = [
            ['to_work', 'К работе', 10],
            ['in_progress', 'В работе', 20],
            ['review', 'Проверка', 30],
            ['testing', 'Тестирование', 40],
            ['done', 'Выполнено', 50],
            ['blocked', 'Заблокировано', 60],
            ['paused', 'Приостановлено', 70],
            ['archived', 'Архивировано', 80],
        ];
        foreach ($statuses as $status) {
            $this->insert('{{%task_statuses}}', [
                'name' => $status[0],
                'label' => $status[1],
                'sort_order' => $status[2],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        $this->dropForeignKey('fk-task_statuses-workflow_id', '{{%task_statuses}}');
        $this->dropTable('{{%task_statuses}}');
    }
}
