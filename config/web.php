<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$modules = require __DIR__ . '/modules.php';
$passportHttpClient = require __DIR__ . '/passport_http_client.php';
$container = __DIR__ . '/container.php';

$config = [
    'id' => $_ENV['APP_NAME'],
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'core\presentation\controller',
    'bootstrap' => ['log'],
    'on beforeRequest' => function () {
        $request = \Yii::$app->request;

        if (strpos($request->getPathInfo(), 'docs/') === 0) {
            return;
        }

        $middleware = Yii::$container->get(
            \core\security\JwtMiddleware::class
        );

        $middleware->handle();
    },
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@core' => dirname(__DIR__) . '/core',
        '@modules' => dirname(__DIR__) . '/modules',
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
            'identityClass' => \core\security\YiiIdentity::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        'errorHandler' => [
            'class' => 'core\infrastructure\handler\JsonErrorHandler',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // User routes
                'GET user/me' => 'user/me',

                // Swagger documentation
                'docs/swagger/json' => 'swagger/json',
                'docs/swagger' => 'swagger/ui',

                // Дефолтный маршрут для OPTIONS (CORS)
                'OPTIONS <any:.*>' => 'site/options',
            ],
        ],
    ],
    'params' => $params,
];

require $container;

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
