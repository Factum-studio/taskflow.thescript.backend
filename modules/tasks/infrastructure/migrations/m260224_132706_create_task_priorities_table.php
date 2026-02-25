<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_priorities}}`.
 */
class m260224_132706_create_task_priorities_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_priorities}}', [
            'id' => $this->primaryKey(),
            'value' => $this->smallInteger()->notNull()->unique(),
            'label' => $this->string(50)->notNull(),
            'color' => $this->string(20),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $priorities = [
            [0, 'Критичный', '#ff0000'],
            [1, 'Высокий', '#ff9900'],
            [2, 'Средний', '#ffff00'],
            [3, 'Низкий', '#00ff00'],
        ];
        foreach ($priorities as $p) {
            $this->insert('{{%task_priorities}}', [
                'value' => $p[0],
                'label' => $p[1],
                'color' => $p[2],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task_priorities}}');
    }
}
