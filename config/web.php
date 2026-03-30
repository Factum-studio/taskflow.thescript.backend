<?php

use core\security\JwtMiddleware;
use core\security\YiiIdentity;
use yii\symfonymailer\Mailer;

$params             = require __DIR__ . '/params.php';
$db                 = require __DIR__ . '/db.php';
$modules            = require __DIR__ . '/modules.php';
$passportHttpClient = require __DIR__ . '/passport_http_client.php';
$container          = __DIR__ . '/container.php';
$diConfigs          = [
    __DIR__ . '/../modules/projects/config/di.php',
    __DIR__ . '/../modules/tasks/config/di.php',
];

$config = [
    'id' => $_ENV['APP_NAME'],
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'core\presentation\controller',
    'bootstrap' => ['log'],
    'on beforeRequest' => function () {
        $request = Yii::$app->request;

        if (strpos($request->getPathInfo(), 'docs/') === 0) {
            return;
        }

        $middleware = Yii::$container->get(
            JwtMiddleware::class
        );

        $middleware->handle();
    },
    'aliases' => [
        '@bower'    => '@vendor/bower-asset',
        '@npm'      => '@vendor/npm-asset',
        '@core'     => dirname(__DIR__) . '/core',
        '@modules'  => dirname(__DIR__) . '/modules',
    ],
    'modules'=>$modules,
    'components' => [
        'passportHttpClient'=>$passportHttpClient,
        'request' => [
            'cookieValidationKey' => $_ENV['COOKIE_VALIDATION_KEY'],
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => YiiIdentity::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        'errorHandler' => [
            'class' => 'core\infrastructure\handler\JsonErrorHandler',
        ],
        'mailer' => [
            'class' => Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 1 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                    'except' => [
                        'yii\web\HttpException:404',
                    ],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                    'categories' => ['projects'],
                    'logFile' => '@app/runtime/logs/projects-error.log',
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['tasks'],
                    'logFile' => '@app/runtime/logs/projects-info.log',
                    'logVars' => [],
                    'enabled' => YII_DEBUG,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                    'categories' => ['tasks'],
                    'logFile' => '@app/runtime/logs/tasks-error.log',
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['tasks'],
                    'logFile' => '@app/runtime/logs/tasks-info.log',
                    'logVars' => [],
                    'enabled' => YII_DEBUG,
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => array_merge(
                require __DIR__ . '/../modules/tasks/config/routing.php',
                require __DIR__ . '/../modules/projects/config/routing.php',
                [
                    // User routes
                    'GET user/me' => 'user/me',

                    // Swagger documentation
                    'docs/swagger/json' => 'swagger/json',
                    'docs/swagger' => 'swagger/ui',

                    // Дефолтный маршрут для OPTIONS (CORS)
                    'OPTIONS <any:.*>' => 'site/options',
                ]
            ),
        ],
    ],
    'params' => $params,
];

require $container;

foreach ($diConfigs as $diConfig) {
    if (file_exists($diConfig)) {
        require $diConfig;
    }
}

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
