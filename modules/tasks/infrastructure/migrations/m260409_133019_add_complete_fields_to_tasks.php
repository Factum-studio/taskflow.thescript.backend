<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

class m260409_133019_add_complete_fields_to_tasks extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%tasks}}', 'complete', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%tasks}}', 'complete');
    }
}
