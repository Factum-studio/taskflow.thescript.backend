<?php

namespace modules\tasks\infrastructure\migrations;

use yii\db\Migration;

class m260316_152704_add_type_to_task_time_intervals extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%task_time_intervals}}', 'type', "ENUM('timer','plan') NOT NULL DEFAULT 'plan'");
        $this->createIndex('idx-task_time_intervals-type', '{{%task_time_intervals}}', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%task_time_intervals}}', 'type');
    }
}
