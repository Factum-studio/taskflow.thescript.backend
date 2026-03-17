<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%board_columns}}`.
 */
class m260317_095225_create_board_columns_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%board_columns}}', [
            'id' => $this->primaryKey(),
            'board_id' => $this->integer()->notNull(),
            'name' => $this->string(50)->notNull(),
            'label' => $this->string(255)->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'is_final' => $this->boolean()->notNull()->defaultValue(false),
            'color' => $this->string(20)->null(),
            'workflow_id' => $this->integer()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-board_columns-board_id', '{{%board_columns}}', 'board_id');
        $this->createIndex('idx-board_columns-sort_order', '{{%board_columns}}', 'sort_order');
        $this->createIndex('idx-board_columns-is_active', '{{%board_columns}}', 'is_active');

        // Уникальность имени в рамках доски
        $this->createIndex('idx-board_columns-board_id-name', '{{%board_columns}}', ['board_id', 'name'], true);

        // Внешний ключ на таблицу boards (будет создана позже в модуле projects)
        // Пока добавим без FK, но с комментарием
        // $this->addForeignKey(
        //     'fk-board_columns-board_id',
        //     '{{%board_columns}}',
        //     'board_id',
        //     '{{%boards}}',
        //     'id',
        //     'CASCADE',
        //     'CASCADE'
        // );
    }

    public function safeDown()
    {
        $this->dropTable('{{%board_columns}}');
    }
}
