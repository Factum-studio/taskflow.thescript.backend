<?php
namespace modules\projects\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project_user}}`.
 */
class m260317_174437_create_project_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%project_user}}', [
            'project_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'role' => $this->string(50)->notNull(), // admin, member
            'invited_by' => $this->integer()->null(),
            'invited_at' => $this->timestamp()->null(),
            'accepted_at' => $this->timestamp()->null(),
            'joined_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(project_id, user_id)',
        ]);

        $this->addForeignKey(
            'fk-project_user-project_id',
            '{{%project_user}}',
            'project_id',
            '{{%project}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-project_user-user_id', '{{%project_user}}', 'user_id');
        $this->createIndex('idx-project_user-role', '{{%project_user}}', 'role');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-project_user-project_id', '{{%project_user}}');
        $this->dropTable('{{%project_user}}');
    }
}
