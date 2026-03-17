<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

class m260317_095250_alter_tasks_add_column_id extends Migration
{
    public function safeUp()
    {
        // Добавляем column_id
        $this->addColumn('{{%tasks}}', 'column_id', $this->integer()->null()->after('board_id'));

        // Создаём индекс для column_id
        $this->createIndex('idx-tasks-column_id', '{{%tasks}}', 'column_id');

        // Удаляем внешний ключ на task_statuses
        $this->dropForeignKey('fk-tasks-status_id', '{{%tasks}}');

        // Удаляем индекс для status_id
        $this->dropIndex('idx-tasks-status_id', '{{%tasks}}');

        // Удаляем столбец status_id
        $this->dropColumn('{{%tasks}}', 'status_id');

        // Добавляем внешний ключ на board_columns
        $this->addForeignKey(
            'fk-tasks-column_id',
            '{{%tasks}}',
            'column_id',
            '{{%board_columns}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // Удаляем таблицу task_statuses
        $this->dropTable('{{%task_statuses}}');
    }

    public function safeDown()
    {
        // Возвращаем status_id
        $this->addColumn('{{%tasks}}', 'status_id', $this->integer()->null());

        // Восстанавливаем индексы и ключи
        $this->createIndex('idx-tasks-status_id', '{{%tasks}}', 'status_id');
        $this->addForeignKey(
            'fk-tasks-status_id',
            '{{%tasks}}',
            'status_id',
            '{{%task_statuses}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // Удаляем column_id и его ключи
        $this->dropForeignKey('fk-tasks-column_id', '{{%tasks}}');
        $this->dropIndex('idx-tasks-column_id', '{{%tasks}}');
        $this->dropColumn('{{%tasks}}', 'column_id');

        // Воссоздаём таблицу task_statuses
        $this->createTable('{{%task_statuses}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->unique(),
            'label' => $this->string(255)->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'workflow_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Добавить внешний ключ на workflows (если нужен)
    }
}
