<?php

use yii\symfonymailer\Mailer;

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
        'sync-users' => 'app\commands\SyncUsersCommand',
        'company-clean' => 'app\commands\CompanyCleanCommand',
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
