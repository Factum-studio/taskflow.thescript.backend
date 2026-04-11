<?php

use core\application\handler\GetCurrentUserQueryHandler;
use core\application\notification\channel\EmailChannel;
use core\application\notification\channel\PushChannel;
use core\application\notification\channel\TelegramChannel;
use core\application\notification\NotificationHub;
use core\application\port\IAuthorRepository;
use core\application\port\ICompanyRepository;
use core\application\port\IEventDispatcher;
use core\application\port\IFeedbackIdeaRepository;
use core\application\port\IFeedbackRatingRepository;
use core\application\port\ILocalUserRepository;
use core\application\port\IPassportGateway;
use core\application\port\IJwtValidator;
use core\application\port\IUrlBuilder;
use core\application\port\IUserRepository;
use core\application\useCase\AuthenticateByJwtUseCase;
use core\application\useCase\GetAuthenticatedUserUseCase;
use core\domain\event\IdeaSubmittedEvent;
use core\domain\event\RatingSubmittedEvent;
use core\infrastructure\event\GlobalEventDispatcher;
use core\infrastructure\http\ApiUrlBuilder;
use core\infrastructure\http\config\ApiEndpointConfig;
use core\infrastructure\listener\SendNonMaxRatingEmailListener;
use core\infrastructure\passport\HttpPassportGateway;
use core\infrastructure\jwt\JwtValidator;
use core\infrastructure\repository\DbAuthorRepository;
use core\infrastructure\repository\DbCompanyRepository;
use core\infrastructure\repository\DbFeedbackIdeaRepository;
use core\infrastructure\repository\DbFeedbackRatingRepository;
use core\infrastructure\repository\DbLocalUserRepository;
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

$container->set(ILocalUserRepository::class, function() {
    return new DbLocalUserRepository();
});

$container->setSingleton(ICompanyRepository::class, function() {
    return new DbCompanyRepository(Yii::$app->db);
});

$container->setSingleton(IAuthorRepository::class, function() {
    return new DbAuthorRepository(Yii::$app->db);
});

$container->set(IFeedbackRatingRepository::class, function() {
    return new DbFeedbackRatingRepository();
});

$container->set(IFeedbackIdeaRepository::class, function() {
    return new DbFeedbackIdeaRepository();
});

$container->setSingleton(GetAuthenticatedUserUseCase::class, function() use ($container) {
    return new GetAuthenticatedUserUseCase(
        $container->get(AuthenticateByJwtUseCase::class),
        $container->get(IUserRepository::class),
        $container->get(ILocalUserRepository::class),
        $container->get(IEventDispatcher::class)
    );
});

$container->setSingleton(JwtMiddleware::class, function() use ($container) {
    return new JwtMiddleware(
        $container->get(GetAuthenticatedUserUseCase::class)
    );
});

$container->setSingleton(GetCurrentUserQueryHandler::class);

$container->set(Redis::class, function () {
    $redis = new Redis();
    $redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
    if (!empty($_ENV['REDIS_PASSWORD'])) {
        $redis->auth($_ENV['REDIS_PASSWORD']);
    }
    return $redis;
});

$container->set(PushChannel::class, function ($container) {
    return new PushChannel($container->get(Redis::class));
});

$container->set(EmailChannel::class, function () {
    return new EmailChannel(
        $_ENV['SENDER_EMAIL'],
        $_ENV['SENDER_NAME']
    );
});

$container->set(TelegramChannel::class);

$container->set(NotificationHub::class, function ($container) {
    return new NotificationHub(
        $container->get(EmailChannel::class),
        $container->get(TelegramChannel::class),
        $container->get(PushChannel::class)
    );
});

$container->set(IEventDispatcher::class, function ($container) {
    /** @var SendNonMaxRatingEmailListener $listener */
    $ratingNonMaxEmailer = $container->get(SendNonMaxRatingEmailListener::class);

    $listeners = [
        RatingSubmittedEvent::class => [
            [$ratingNonMaxEmailer, 'handle'],
        ],
    ];

    return new GlobalEventDispatcher($container, $listeners);
});
