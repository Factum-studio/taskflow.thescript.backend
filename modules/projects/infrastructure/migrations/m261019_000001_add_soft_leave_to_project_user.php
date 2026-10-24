<?php

declare(strict_types=1);

namespace modules\projects\infrastructure\migrations;

use yii\db\Migration;

/** Preserve membership history when a user leaves a project. */
class m261019_000001_add_soft_leave_to_project_user extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%project_user}}', 'is_left', 'TINYINT(1) NOT NULL DEFAULT 0');
        $this->addColumn('{{%project_user}}', 'left_at', 'TIMESTAMP NULL');
        $this->createIndex('idx-project_user-active', '{{%project_user}}', ['project_id', 'is_left']);
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-project_user-active', '{{%project_user}}');
        $this->dropColumn('{{%project_user}}', 'left_at');
        $this->dropColumn('{{%project_user}}', 'is_left');
    }
}
