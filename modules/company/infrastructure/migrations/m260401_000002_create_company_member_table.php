<?php

declare(strict_types=1);

namespace modules\company\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company_member}}`.
 */
class m260401_000002_create_company_member_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%company_member}}', [
            'company_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'role' => "ENUM('owner','admin','member') NOT NULL DEFAULT 'member'",
            'invited_by' => $this->integer()->null(),
            'invited_at' => $this->timestamp()->null(),
            'accepted_at' => $this->timestamp()->null(),
            'joined_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(company_id, user_id)',
        ]);

        $this->addForeignKey(
            'fk-company_member-company_id',
            '{{%company_member}}',
            'company_id',
            '{{%company}}',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $this->createIndex('idx-company_member-user_id', '{{%company_member}}', 'user_id');
        $this->createIndex('idx-company_member-role', '{{%company_member}}', 'role');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-company_member-company_id', '{{%company_member}}');
        $this->dropTable('{{%company_member}}');
    }
}
