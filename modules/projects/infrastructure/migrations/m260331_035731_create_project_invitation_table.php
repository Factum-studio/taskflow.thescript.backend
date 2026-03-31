<?php
namespace modules\projects\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project_invitation}}`.
 */
class m260331_035731_create_project_invitation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%project_invitation}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'email' => $this->string(255)->notNull(),
            'invited_by' => $this->integer()->notNull(),
            'token' => $this->string(64)->notNull()->unique(),
            'status' => "ENUM('pending','accepted','expired','cancelled') NOT NULL DEFAULT 'pending'",
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'expires_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-project_invitation-project_id',
            '{{%project_invitation}}',
            'project_id',
            '{{%project}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx-project_invitation-token', '{{%project_invitation}}', 'token');
        $this->createIndex('idx-project_invitation-project-email-status', '{{%project_invitation}}', ['project_id', 'email', 'status']);
        $this->createIndex('idx-project_invitation-expires_at', '{{%project_invitation}}', 'expires_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%project_invitation}}');
    }
}
