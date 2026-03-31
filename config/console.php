<?php

$params                 = require __DIR__ . '/params.php';
$db                     = require __DIR__ . '/db.php';
$redis                  = require __DIR__ . '/redis.php';
$migrationNamespaces    = require __DIR__ . '/migration_namespaces.php';
$passportHttpClient     = require __DIR__ . '/passport_http_client.php';
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
    'id' => $_ENV['APP_NAME'],
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
        'passportHttpClient' => $passportHttpClient,
        'redis'=> $redis,
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
    'controllerMap' => [
        'sync-users' => 'app\commands\SyncUsersCommand',
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
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
    // configuration adjustments for 'dev' environment
    // requires version `2.1.21` of yii2-debug module
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
