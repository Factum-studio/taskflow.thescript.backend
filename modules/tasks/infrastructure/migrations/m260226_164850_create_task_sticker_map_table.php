<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_sticker_map}}`.
 */
class m260226_164850_create_task_sticker_map_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_sticker_map}}', [
            'task_id' => $this->integer()->notNull(),
            'sticker_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(task_id, sticker_id)',
        ]);

        $this->createIndex('idx-task_sticker_map-sticker_id', '{{%task_sticker_map}}', 'sticker_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task_sticker_map}}');
    }
}
