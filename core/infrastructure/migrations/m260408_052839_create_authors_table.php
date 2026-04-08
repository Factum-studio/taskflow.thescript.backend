<?php
namespace core\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%authors}}`.
 */
class m260408_052839_create_authors_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%author}}', [
            'id'            => $this->primaryKey(),
            'user_id'       => $this->integer()->notNull()->unique(),
            'phrase'        => $this->string(50)->notNull(),
            'badges'        => $this->json()->notNull(),
            'created_at'    => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'    => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-author-user_id',
            '{{%author}}',
            'user_id',
            '{{%user}}',
            'user_id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-author-user_id', '{{%author}}');
        $this->dropTable('{{%author}}');
    }
}
