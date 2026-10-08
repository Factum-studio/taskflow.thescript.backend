<?php

declare(strict_types=1);

use core\application\handler\SyncUserHandler;
use modules\passport\auth\application\handler\HandleSsoCallbackHandler;
use modules\passport\auth\application\handler\InitiateSsoLoginHandler;
use modules\passport\auth\application\port\PassportAuthPort;
use modules\passport\auth\infrastructure\http\PassportHttpClient;
use modules\passport\auth\presentation\middleware\PassportAuthMiddleware;
use yii\httpclient\Client;

$params = require __DIR__ . '/params.php';

foreach ([
    'oauth2_client_id' => 'OAUTH2_CLIENT_ID',
    'oauth2_client_secret' => 'OAUTH2_CLIENT_SECRET',
    'oauth2_public_key_path' => 'OAUTH2_PUBLIC_KEY_PATH',
] as $key => $envName) {
    if (($params[$key] ?? '') === '') {
        throw new RuntimeException($envName . ' is required for passport/auth');
    }
}

$container = Yii::$container;

$container->set(Client::class, static function (): Client {
    return new Client([
        'transport' => [
            'class' => 'yii\httpclient\CurlTransport',
        ],
    ]);
});

$container->set(PassportAuthPort::class, static function () use ($params): PassportAuthPort {
    return new PassportHttpClient(
        Yii::$container->get(Client::class),
        $params['passport_url'],
        $params['oauth2_client_id'],
        $params['oauth2_client_secret'],
        $params['app_url'] . '/auth/callback',
        $params['scopes'],
    );
});

$container->set(InitiateSsoLoginHandler::class, static fn (): InitiateSsoLoginHandler => new InitiateSsoLoginHandler(
    Yii::$container->get(PassportAuthPort::class),
));

$container->set(HandleSsoCallbackHandler::class, static fn (): HandleSsoCallbackHandler => new HandleSsoCallbackHandler(
    Yii::$container->get(PassportAuthPort::class),
    Yii::$container->get(SyncUserHandler::class),
));

$container->set(PassportAuthMiddleware::class, static function () use ($params): PassportAuthMiddleware {
    return new PassportAuthMiddleware(
        Yii::$container->get(PassportAuthPort::class),
        $params['public_routes'],
        $params['oauth2_public_key_path'],
        $params['oauth2_client_id'],
        $params['access_token_cookie_name'],
        $params['refresh_token_cookie_name'],
    );
});
