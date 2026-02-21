<?php

use core\application\port\IPassportGateway;
use core\application\port\IJwtValidator;
use core\infrastructure\passport\HttpPassportGateway;
use core\infrastructure\jwt\JwtValidator;
use yii\httpclient\Client;

$container = Yii::$container;

$container->set(IPassportGateway::class, function () {
    return new HttpPassportGateway(
        Yii::$app->get('passportHttpClient')
    );
});

$container->set(IJwtValidator::class, function () {
    $config = Yii::$app->params['jwt'];

    return new JwtValidator(
        $config['secret'],
        $config['issuer'],
        $config['audience']
    );
});
