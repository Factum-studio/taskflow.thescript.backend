<?php
namespace core\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company}}`.
 */
class m260401_024424_create_company_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%company}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->unique(),
            'description' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Добавляем колонку company_id и post в таблицу user
        $this->addColumn('{{%user}}', 'company_id', $this->integer()->null());
        $this->addForeignKey('fk-user-company_id', '{{%user}}', 'company_id', '{{%company}}', 'id', 'SET NULL', 'CASCADE');
        $this->addColumn('{{%user}}', 'post', $this->string(255)->null());

        $this->insert('{{%company}}', [
            'name' => 'Script Agency',
            'description' => 'digital-агентство заказной разработки',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-user-company_id', '{{%user}}');
        $this->dropColumn('{{%user}}', 'company_id');
        $this->dropColumn('{{%user}}', 'post');
        $this->dropTable('{{%company}}');
    }
}
