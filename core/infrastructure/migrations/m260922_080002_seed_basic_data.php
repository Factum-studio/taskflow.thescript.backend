<?php

declare(strict_types=1);

namespace core\infrastructure\migrations;

use yii\db\Migration;

class m260922_080002_seed_basic_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->batchInsert('roles', ['name', 'description'], [
            ['user', 'Зарегистрированный пользователь'],
            ['admin', 'Админ, имеет все полномочия в системе'],
        ]);
    }
}
