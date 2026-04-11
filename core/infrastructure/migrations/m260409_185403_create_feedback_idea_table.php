<?php
namespace core\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%feedback_idea}}`.
 */
class m260409_185403_create_feedback_idea_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%feedback_idea}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'type' => "ENUM('idea', 'bug', 'feature', 'improvement', 'question') NOT NULL",
            'comment' => $this->text()->notNull(),
            'is_implemented' => $this->boolean()->defaultValue(false),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-feedback_idea-user_id',
            '{{%feedback_idea}}',
            'user_id',
            '{{%user}}',
            'user_id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-feedback_idea-type', '{{%feedback_idea}}', 'type');
        $this->createIndex('idx-feedback_idea-is_implemented', '{{%feedback_idea}}', 'is_implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%feedback_idea}}');
    }
}
