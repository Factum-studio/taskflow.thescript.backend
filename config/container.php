<?php

use core\application\port\IPassportGateway;
use core\application\port\IJwtValidator;
use core\application\port\IUrlBuilder;
use core\infrastructure\http\ApiUrlBuilder;
use core\infrastructure\http\config\ApiEndpointConfig;
use core\infrastructure\passport\HttpPassportGateway;
use core\infrastructure\jwt\JwtValidator;

$container = Yii::$container;

$container->setSingleton(ApiEndpointConfig::class, function() {
    return new ApiEndpointConfig([
        'user' => 'user',
        'contact' => 'contact',
        'city' => 'city',
        'post' => 'post',
    ]);
});

$container->setSingleton(IUrlBuilder::class, function() use ($container) {
    $baseUrl = $_ENV['USER_SERVICE_BASE_URL'] ?? '';

    return (new ApiUrlBuilder($baseUrl))
        ->withVersion('v1');
});

$container->setSingleton(IPassportGateway::class, function() use ($container) {
    return new HttpPassportGateway(
        Yii::$app->passportHttpClient,
        $container->get(IUrlBuilder::class),
        $container->get(ApiEndpointConfig::class)
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
