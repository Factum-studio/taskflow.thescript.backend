<?php

declare(strict_types=1);

namespace core\infrastructure\migrations;

use yii\db\Migration;

final class m260922_080000_create_users_table extends Migration
{
    public function safeUp(): void
    {
        $opts = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->createTable('{{%users}}', [
            'id'             => $this->bigPrimaryKey(),
            'passport_id'    => $this->string(64)->notNull(),
            'surname'        => $this->string(128)->notNull(),
            'name'           => $this->string(128)->notNull(),
            'patronymic'     => $this->string(128)->null(),
            'email'          => $this->string(255)->notNull(),
            'post'           => $this->string(255)->null(),
            'is_owner'       => $this->boolean()->notNull()->defaultValue(false),
            'synced_at'      => $this->timestamp()->null(),
            'created_at'     => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'     => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $opts);

        $this->createIndex('uniq_users_passport', '{{%users}}', 'passport_id', true);
        $this->createIndex('uniq_users_email', '{{%users}}', 'email', true);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%users}}');
    }
}
