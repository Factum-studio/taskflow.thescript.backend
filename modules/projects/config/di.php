<?php

use core\domain\event\UserFirstLoginEvent;
use modules\projects\application\port\IProjectAccess;
use core\application\port\IEventDispatcher as GlobalEventDispatcher;
use modules\projects\domain\event\IEventDispatcher as ProjectEventDispatcher;
use modules\projects\domain\event\ProjectCreatedEvent;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\infrastructure\access\ProjectAccess;
use modules\projects\infrastructure\event\DispatchingEventDecorator;
use modules\projects\infrastructure\listener\AddOwnerAsMemberListener;
use modules\projects\infrastructure\listener\CreatePersonalProjectOnUserFirstLogin;
use modules\projects\infrastructure\repository\DbProjectRepository;
use modules\projects\infrastructure\repository\DbBoardRepository;
use modules\projects\infrastructure\repository\DbProjectUserRepository;
use modules\projects\application\assembler\ProjectDtoAssembler;
use modules\projects\application\assembler\BoardDtoAssembler;
use modules\projects\infrastructure\event\ModuleEventDispatcher;
use modules\projects\application\assembler\ProjectUserDtoAssembler;
use yii\di\Container;

Yii::$container->set(IProjectRepository::class, function () {
    return new DbProjectRepository(Yii::$app->db);
});

Yii::$container->set(IBoardRepository::class, function () {
    return new DbBoardRepository(Yii::$app->db);
});

Yii::$container->set(IProjectUserRepository::class, function () {
    return new DbProjectUserRepository(Yii::$app->db);
});

Yii::$container->set(ProjectDtoAssembler::class);
Yii::$container->set(BoardDtoAssembler::class);
Yii::$container->set(ProjectUserDtoAssembler::class);

Yii::$container->set(
    IProjectAccess::class,
    ProjectAccess::class
);

Yii::$container->set(AddOwnerAsMemberListener::class);

Yii::$container->set(ModuleEventDispatcher::class, function (Container $container) {
    /** @var AddOwnerAsMemberListener $listener */
    $listener = $container->get(AddOwnerAsMemberListener::class);

    $listeners = [
        ProjectCreatedEvent::class => [
            [$listener, 'handleProjectCreated'],
        ],
    ];

    return new ModuleEventDispatcher($listeners);
});

$forwardEvents = [
    ProjectCreatedEvent::class,
];

Yii::$container->set(ProjectEventDispatcher::class, function (Container $container) use ($forwardEvents) {
    return new DispatchingEventDecorator(
        $container->get(ModuleEventDispatcher::class),
        $container->get(GlobalEventDispatcher::class),
        $forwardEvents
    );
});

$globalDispatcher = Yii::$container->get(GlobalEventDispatcher::class);
$globalDispatcher->addListener(UserFirstLoginEvent::class, CreatePersonalProjectOnUserFirstLogin::class);