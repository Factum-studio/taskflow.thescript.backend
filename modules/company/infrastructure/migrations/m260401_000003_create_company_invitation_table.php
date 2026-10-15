<?php

declare(strict_types=1);

namespace modules\company\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company_invitation}}`.
 */
class m260401_000003_create_company_invitation_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%company_invitation}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'email' => $this->string(255)->notNull(),
            'invited_by' => $this->integer()->notNull(),
            'token' => $this->string(64)->notNull()->unique(),
            'status' => "ENUM('pending','accepted','expired','cancelled') NOT NULL DEFAULT 'pending'",
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'expires_at' => $this->timestamp()->notNull(),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-company_invitation-company_id',
            '{{%company_invitation}}',
            'company_id',
            '{{%company}}',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $this->createIndex('idx-company_invitation-token', '{{%company_invitation}}', 'token');
        $this->createIndex('idx-company_invitation-company-email-status', '{{%company_invitation}}', ['company_id', 'email', 'status']);
        $this->createIndex('idx-company_invitation-expires_at', '{{%company_invitation}}', 'expires_at');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-company_invitation-company_id', '{{%company_invitation}}');
        $this->dropTable('{{%company_invitation}}');
    }
}
