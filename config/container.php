<?php

declare(strict_types=1);

use core\application\handler\AssignUserRoleHandler;
use core\application\handler\CreateRoleHandler;
use core\application\handler\DeleteRoleHandler;
use core\application\handler\GetRoleHandler;
use core\application\handler\GetUserHandler;
use core\application\handler\ListRoleHandler;
use core\application\handler\ListUserHandler;
use core\application\handler\ListUserRoleHandler;
use core\application\handler\RemoveUserRoleHandler;
use core\application\handler\SyncUserHandler;
use core\application\handler\UpdateRoleHandler;
use core\application\notification\channel\EmailChannel;
use core\application\notification\channel\PushChannel;
use core\application\notification\channel\TelegramChannel;
use core\application\notification\NotificationHub;
use core\application\port\IEventDispatcher;
use core\application\port\IRoleRepository;
use core\application\port\ITransactionManager;
use core\application\port\IUserRepository;
use core\application\port\IUserRoleRepository;
use core\application\port\IUserRoleSearch;
use core\domain\event\RatingSubmittedEvent;
use core\infrastructure\event\GlobalEventDispatcher;
use core\infrastructure\listener\SendNonMaxRatingEmailListener;
use core\infrastructure\repository\DbRoleRepository;
use core\infrastructure\repository\DbTransactionManager;
use core\infrastructure\repository\DbUserRepository;
use core\infrastructure\repository\DbUserRoleRepository;
use core\infrastructure\repository\DbUserRoleSearch;

$container = Yii::$container;

// ---------- Репозитории ----------

$container->setSingleton(IRoleRepository::class, function () {
    return new DbRoleRepository();
});

$container->setSingleton(IUserRoleRepository::class, function () {
    return new DbUserRoleRepository();
});

$container->setSingleton(IUserRepository::class, function () {
    return new DbUserRepository();
});

$container->setSingleton(IUserRoleSearch::class, function () {
    return new DbUserRoleSearch();
});

$container->setSingleton(ITransactionManager::class, function () {
    return new DbTransactionManager();
});

// ---------- Хендлеры (команды и запросы) ----------

// Роли
$container->setSingleton(CreateRoleHandler::class);
$container->setSingleton(UpdateRoleHandler::class);
$container->setSingleton(DeleteRoleHandler::class);
$container->setSingleton(GetRoleHandler::class);
$container->setSingleton(ListRoleHandler::class);

// Роли пользователей
$container->setSingleton(AssignUserRoleHandler::class);
$container->setSingleton(RemoveUserRoleHandler::class);
$container->setSingleton(ListUserRoleHandler::class);

// Пользователи
$container->setSingleton(SyncUserHandler::class);
$container->setSingleton(GetUserHandler::class);
$container->setSingleton(ListUserHandler::class);

// ---------- Redis ----------
$container->set(Redis::class, function () {
    $redis = new Redis();
    $redis->connect($_ENV['REDIS_HOST'], (int)$_ENV['REDIS_PORT']);
    if (!empty($_ENV['REDIS_PASSWORD'])) {
        $redis->auth($_ENV['REDIS_PASSWORD']);
    }
    return $redis;
});

// ---------- Notifications ----------
$container->set(PushChannel::class, function ($container) {
    return new PushChannel($container->get(Redis::class));
});

$container->set(EmailChannel::class, function () {
    return new EmailChannel(
        $_ENV['SENDER_EMAIL'],
        $_ENV['SENDER_NAME'],
    );
});

$container->set(TelegramChannel::class);
$container->set(NotificationHub::class, function ($container) {
    return new NotificationHub(
        $container->get(EmailChannel::class),
        $container->get(TelegramChannel::class),
        $container->get(PushChannel::class),
    );
});

// ---------- Events ----------

$container->setSingleton(IEventDispatcher::class, function ($container) {
    return new GlobalEventDispatcher($container, []);
});
