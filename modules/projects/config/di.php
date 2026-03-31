<?php

use core\domain\event\UserFirstLoginEvent;
use modules\projects\application\port\IProjectAccess;
use core\application\port\IEventDispatcher as GlobalEventDispatcher;
use modules\projects\domain\event\BoardCreatedEvent;
use modules\projects\domain\event\IEventDispatcher as ProjectEventDispatcher;
use modules\projects\domain\event\ProjectCreatedEvent;
use modules\projects\domain\event\ProjectMemberAddedEvent;
use modules\projects\domain\event\ProjectMemberRemovedEvent;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\infrastructure\access\ProjectAccess;
use modules\projects\infrastructure\event\DispatchingEventDecorator;
use modules\projects\infrastructure\listener\AddOwnerAsMemberListener;
use modules\projects\infrastructure\listener\CreatePersonalProjectOnUserFirstLogin;
use modules\projects\infrastructure\listener\ProjectLoggerListener;
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
    $ownerListener = $container->get(AddOwnerAsMemberListener::class);
    /** @var ProjectLoggerListener $logger */
    $logger = $container->get(ProjectLoggerListener::class);

    $listeners = [
        ProjectCreatedEvent::class => [
            [$ownerListener, 'handleProjectCreated'],
            [$logger, 'handleProjectCreated'],
        ],
        ProjectMemberAddedEvent::class => [
            [$logger, 'handleProjectMemberAdded'],
        ],
        ProjectMemberRemovedEvent::class => [
            [$logger, 'handleProjectMemberRemoved'],
        ],
        BoardCreatedEvent::class => [
            [$logger, 'handleBoardCreated'],
        ],
    ];

    return new ModuleEventDispatcher($listeners);
});

$forwardEvents = [
    ProjectCreatedEvent::class,
    ProjectMemberAddedEvent::class,
    ProjectMemberRemovedEvent::class,
    BoardCreatedEvent::class,
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