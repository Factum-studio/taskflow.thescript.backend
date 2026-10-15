<?php

declare(strict_types=1);

namespace modules\company\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company}}`.
 */
class m260401_000001_create_company_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%company}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->unique(),
            'owner_id' => $this->integer()->notNull(),
            'status' => "ENUM('active','archived') NOT NULL DEFAULT 'active'",
            'verified' => $this->boolean()->notNull()->defaultValue(false),
            'email' => $this->string(255)->null(),
            'website' => $this->string(255)->null(),
            'phone' => $this->string(50)->null(),
            'country' => $this->string(100)->null(),
            'city' => $this->string(100)->null(),
            'address' => $this->string(255)->null(),
            'tax_num' => $this->string(50)->null(),
            'registration_num' => $this->string(50)->null(),
            'description' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-company-owner_id', '{{%company}}', 'owner_id');
        $this->createIndex('idx-company-status', '{{%company}}', 'status');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%company}}');
    }
}
