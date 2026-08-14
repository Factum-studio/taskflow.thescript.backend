<?php
namespace modules\projects\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project}}`.
 */
class m260317_174252_create_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%project}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'type' => $this->string(50)->notNull(), // personal, collaborative, corporate
            'owner_id' => $this->integer()->notNull(),
            'settings' => $this->json()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-project-owner_id', '{{%project}}', 'owner_id');
        $this->createIndex('idx-project-type', '{{%project}}', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%project}}');
    }
}
