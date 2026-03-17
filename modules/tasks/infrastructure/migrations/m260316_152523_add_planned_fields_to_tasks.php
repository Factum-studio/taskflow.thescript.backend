<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

class m260316_152523_add_planned_fields_to_tasks extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%tasks}}', 'planned_start', $this->timestamp()->null());
        $this->addColumn('{{%tasks}}', 'planned_end', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%tasks}}', 'planned_start');
        $this->dropColumn('{{%tasks}}', 'planned_end');
    }
}
