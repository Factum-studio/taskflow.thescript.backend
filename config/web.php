<?php

declare(strict_types=1);

use core\security\YiiIdentity;
use modules\passport\auth\presentation\middleware\PassportAuthMiddleware;
use modules\rbac\presentation\middleware\RbacMiddleware;
use yii\symfonymailer\Mailer;

$params         = require __DIR__ . '/params.php';
$db             = require __DIR__ . '/db.php';
$modules        = require __DIR__ . '/modules.php';
$container      = __DIR__ . '/container.php';
$diConfigs          = [
    __DIR__ . '/../modules/passport/auth/config/di.php',
    __DIR__ . '/../modules/rbac/config/di.php',
    __DIR__ . '/../modules/projects/config/di.php',
    __DIR__ . '/../modules/tasks/config/di.php',
    __DIR__ . '/../modules/feedback/config/di.php',
];

$config = [
    'id' => $_ENV['APP_NAME'],
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'core\presentation\controller',
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower'    => '@vendor/bower-asset',
        '@npm'      => '@vendor/npm-asset',
        '@core'     => dirname(__DIR__) . '/core',
        '@modules'  => dirname(__DIR__) . '/modules',
    ],
    'on beforeRequest' => static function (): void {
        Yii::$container->get(PassportAuthMiddleware::class)->handle();
        Yii::$container->get(RbacMiddleware::class)->handle();
    },
    'modules' => $modules,
    'components' => [
        'request' => [
            'cookieValidationKey' => $_ENV['COOKIE_VALIDATION_KEY'],
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser',
            ],
        ],
        'response' => [
            'format'    => yii\web\Response::FORMAT_JSON,
            'charset'   => 'UTF-8',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass'     => YiiIdentity::class,
            'enableAutoLogin'   => false,
            'enableSession'     => false,
        ],
        'errorHandler' => [
            'class' => 'core\infrastructure\handler\JsonErrorHandler',
        ],
        'mailer' => [
            'class'             => Mailer::class,
            'viewPath'          => '@app/mail',
            'useFileTransport'  => false,
            'transport'        => [
                'scheme'   => $_ENV['MAILER_SCHEME'],
                'host'     => $_ENV['MAILER_HOST'],
                'port'     => $_ENV['MAILER_PORT'],
                'username' => $_ENV['MAILER_USERNAME'],
                'password' => $_ENV['MAILER_PASSWORD'],
                'encryption' => $_ENV['MAILER_ENCRYPTION'],
                'options' => [
                    'verify_peer' => 0,
                    'verify_peer_name' => 0,
                ],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 1 : 0,
            'targets' => [
                [
                    'class'     => 'yii\log\FileTarget',
                    'levels'    => ['error', 'warning'],
                    'logVars'   => [],
                    'except' => [
                        'yii\web\HttpException:404',
                    ],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl'   => true,
            'showScriptName'    => false,
            'rules' =>  array_merge(
                require __DIR__ . '/../modules/passport/auth/config/routing.php',
                require __DIR__ . '/../modules/rbac/config/routing.php',
                require __DIR__ . '/../modules/projects/config/routing.php',
                require __DIR__ . '/../modules/tasks/config/routing.php',
                require __DIR__ . '/../modules/feedback/config/routing.php',
                [
                    // Role
                    'GET roles'                     => 'role/index',
                    'GET roles/<id:\d+>'            => 'role/view',
                    'POST roles'                    => 'role/create',
                    'PUT roles/<id:\d+>'            => 'role/update',
                    'DELETE roles/<id:\d+>'         => 'role/delete',

                    // User
                    'GET users'                     => 'user/index',
                    'GET users/<id:\d+>'            => 'user/view',
                    'GET users/sync'                => 'user/sync',

                    // UserRole
                    'GET user-roles'                => 'user-role/index',
                    'POST user-roles'               => 'user-role/create',
                    'DELETE user-roles/<id:\d+>'    => 'user-role/delete',

                    // Welcome
                    'GET /'                         => 'welcome/index',

                    // Swagger documentation
                    'docs/swagger/json'             => 'swagger/json',
                    'docs/swagger'                  => 'swagger/ui',

                    // Project documentation
                    'docs'                          => 'docs/ui',
                    'docs/<page:.*>'                => 'docs/ui',

                    // Дефолтный маршрут для OPTIONS (CORS)
                    'OPTIONS <any:.*>'              => 'site/options',
                ],
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
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
