<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stickers}}`.
 */
class m260226_164536_create_stickers_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stickers}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'type' => "ENUM('system', 'user') NOT NULL",
            'project_id' => $this->integer()->null(),
            'data' => $this->json()->null(),
            'color' => $this->string(20)->null(),
            'created_by' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-stickers-name', '{{%stickers}}', 'name');
        $this->createIndex('idx-stickers-type', '{{%stickers}}', 'type');
        $this->createIndex('idx-stickers-project_id', '{{%stickers}}', 'project_id');
        // !!!Важно ё моё у нас уникальность имени должна обеспечиваться в рамках проекта для пользовательских и глобально для системных, делаем на уровне приложения ес что
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%stickers}}');
    }
}
