<?php

declare(strict_types=1);

namespace modules\feedback\infrastructure\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%feedback_rating}}`.
 */
class m261007_080000_create_feedback_rating_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%feedback_rating}}', [
            'id'            => $this->primaryKey(),
            'user_id'       => $this->bigInteger()->notNull()->unique(),
            'speed'         => $this->tinyInteger()->notNull(),
            'functionality' => $this->tinyInteger()->notNull(),
            'design'        => $this->tinyInteger()->notNull(),
            'usability'     => $this->tinyInteger()->notNull(),
            'created_at'    => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at'    => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-feedback_rating-user_id',
            '{{%feedback_rating}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $this->execute("ALTER TABLE {{%feedback_rating}} ADD CONSTRAINT chk_speed_range CHECK (speed BETWEEN 0 AND 5)");
        $this->execute("ALTER TABLE {{%feedback_rating}} ADD CONSTRAINT chk_functionality_range CHECK (functionality BETWEEN 0 AND 5)");
        $this->execute("ALTER TABLE {{%feedback_rating}} ADD CONSTRAINT chk_design_range CHECK (design BETWEEN 0 AND 5)");
        $this->execute("ALTER TABLE {{%feedback_rating}} ADD CONSTRAINT chk_usability_range CHECK (usability BETWEEN 0 AND 5)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%feedback_rating}}');
    }
}
