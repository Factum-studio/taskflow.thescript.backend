<?php

use yii\symfonymailer\Mailer;
declare(strict_types=1);

$params                 = require __DIR__ . '/params.php';
$db                     = require __DIR__ . '/db.php';
$redis                  = require __DIR__ . '/redis.php';
$migrationNamespaces    = require __DIR__ . '/migration_namespaces.php';
$diConfigs          = [
    __DIR__ . '/../modules/projects/config/di.php',
    __DIR__ . '/../modules/tasks/config/di.php',
];

require __DIR__ . '/container.php';

foreach ($diConfigs as $diConfig) {
    if (file_exists($diConfig)) {
        require $diConfig;
    }
}

$config = [
    'id' => $_ENV['APP_NAME'].'-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower'    => '@vendor/bower-asset',
        '@npm'      => '@vendor/npm-asset',
        '@tests'    => '@app/tests',
        '@core'     => dirname(__DIR__) . '/core',
        '@modules'  => dirname(__DIR__) . '/modules',
    ],
    'components' => [
        'redis'=> $redis,
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis',
        ],
        'mailer' => [
            'class' => Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
//                'dsn'           => $_ENV['MAILER_DSN'],
                'scheme'        => $_ENV['MAILER_SCHEME'],
                'host'          => $_ENV['MAILER_HOST'],
                'port'          => $_ENV['MAILER_PORT'],
                'username'      => $_ENV['MAILER_USERNAME'],
                'password'      => $_ENV['MAILER_PASSWORD'],
                'encryption'    => $_ENV['MAILER_ENCRYPTION'],
            ],
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
    'controllerMap' => [
        'email-send' => 'app\commands\EmailSendCommand',
//        'fixture' => [ // Fixture generation command line.
//            'class' => 'yii\faker\FixtureController',
//        ],
        'migrate' => [
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationNamespaces' => $migrationNamespaces,
        ],
    ],
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];
}

return $config;
