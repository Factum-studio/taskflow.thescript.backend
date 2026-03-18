<?php
namespace modules\projects\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%board}}`.
 */
class m260317_174349_create_board_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%board}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'created_by' => $this->integer()->notNull(),
            'settings' => $this->json()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-board-project_id',
            '{{%board}}',
            'project_id',
            '{{%project}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-board-project_id', '{{%board}}', 'project_id');
        $this->createIndex('idx-board-created_by', '{{%board}}', 'created_by');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-board-project_id', '{{%board}}');
        $this->dropTable('{{%board}}');
    }
}
