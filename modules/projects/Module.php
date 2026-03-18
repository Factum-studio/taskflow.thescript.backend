<?php

namespace modules\projects;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public function init(): void
    {
        parent::init();
        $this->loadDiConfig();
    }

    /**
     * Загружает конфигурацию DI-контейнера модуля
     */
    private function loadDiConfig(): void
    {
        $diConfig = __DIR__ . '/config/di.php';
        if (file_exists($diConfig)) {
            require $diConfig;
        }
    }
}