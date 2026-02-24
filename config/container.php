<?php

use core\application\handler\GetCurrentUserQueryHandler;
use core\application\port\IPassportGateway;
use core\application\port\IJwtValidator;
use core\application\port\IUrlBuilder;
use core\application\port\IUserRepository;
use core\application\useCase\AuthenticateByJwtUseCase;
use core\application\useCase\GetAuthenticatedUserUseCase;
use core\infrastructure\http\ApiUrlBuilder;
use core\infrastructure\http\config\ApiEndpointConfig;
use core\infrastructure\passport\HttpPassportGateway;
use core\infrastructure\jwt\JwtValidator;
use core\infrastructure\repository\PassportUserRepository;
use core\presentation\controller\UserController;
use core\security\JwtMiddleware;

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
    return (new ApiUrlBuilder($baseUrl))->withVersion('v1');
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

$container->setSingleton(AuthenticateByJwtUseCase::class, function() use ($container) {
    return new AuthenticateByJwtUseCase(
        $container->get(IJwtValidator::class)
    );
});

$container->setSingleton(IUserRepository::class, function() use ($container) {
    return new PassportUserRepository(
        $container->get(IPassportGateway::class),
        Yii::$app->cache,
        3600
    );
});

$container->setSingleton(GetAuthenticatedUserUseCase::class, function() use ($container) {
    return new GetAuthenticatedUserUseCase(
        $container->get(AuthenticateByJwtUseCase::class),
        $container->get(IUserRepository::class)
    );
});

$container->setSingleton(JwtMiddleware::class, function() use ($container) {
    return new JwtMiddleware(
        $container->get(GetAuthenticatedUserUseCase::class)
    );
});

$container->setSingleton(GetCurrentUserQueryHandler::class);
