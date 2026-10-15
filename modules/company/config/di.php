<?php

declare(strict_types=1);

use modules\company\application\assembler\CompanyDtoAssembler;
use modules\company\application\assembler\CompanyMemberDtoAssembler;
use modules\company\application\handler\AcceptInvitationHandler;
use modules\company\application\handler\ArchiveCompanyHandler;
use modules\company\application\handler\CancelInvitationHandler;
use modules\company\application\handler\ChangeMemberRoleHandler;
use modules\company\application\handler\CreateCompanyHandler;
use modules\company\application\handler\DeleteCompanyHandler;
use modules\company\application\handler\GetCompanyHandler;
use modules\company\application\handler\InviteUserHandler;
use modules\company\application\handler\ListCompaniesHandler;
use modules\company\application\handler\RemoveCompanyMemberHandler;
use modules\company\application\handler\TransferOwnershipHandler;
use modules\company\application\handler\UnarchiveCompanyHandler;
use modules\company\application\handler\UpdateCompanyHandler;
use modules\company\application\handler\VerifyCompanyHandler;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\event\CompanyArchivedEvent;
use modules\company\domain\event\CompanyCreatedEvent;
use modules\company\domain\event\CompanyInvitationAcceptedEvent;
use modules\company\domain\event\CompanyInvitationCreatedEvent;
use modules\company\domain\event\CompanyMemberAddedEvent;
use modules\company\domain\event\CompanyMemberRemovedEvent;
use modules\company\domain\event\CompanyTransferOwnershipEvent;
use modules\company\domain\event\CompanyUpdatedEvent;
use modules\company\domain\event\CompanyVerifiedEvent;
use modules\company\domain\event\IEventDispatcher as CompanyEventDispatcher;
use modules\company\domain\repository\ICompanyInvitationRepository;
use modules\company\domain\repository\ICompanyMemberRepository;
use modules\company\domain\repository\ICompanyRepository;
use modules\company\infrastructure\access\CompanyAccess;
use modules\company\infrastructure\event\DispatchingEventDecorator;
use modules\company\infrastructure\event\ModuleEventDispatcher;
use modules\company\infrastructure\repository\DbCompanyInvitationRepository;
use modules\company\infrastructure\repository\DbCompanyMemberRepository;
use modules\company\infrastructure\repository\DbCompanyRepository;
use core\application\port\IEventDispatcher as GlobalEventDispatcher;
use yii\di\Container;

Yii::$container->set(ICompanyRepository::class, function () {
    return new DbCompanyRepository(Yii::$app->db);
});

Yii::$container->set(ICompanyMemberRepository::class, function () {
    return new DbCompanyMemberRepository(Yii::$app->db);
});

Yii::$container->set(ICompanyInvitationRepository::class, function () {
    return new DbCompanyInvitationRepository(Yii::$app->db);
});

Yii::$container->set(CompanyDtoAssembler::class);
Yii::$container->set(CompanyMemberDtoAssembler::class);

Yii::$container->set(
    ICompanyAccess::class,
    CompanyAccess::class,
);

// Регистрируем хендлеры (для DI в конструкторах контроллеров)
Yii::$container->set(CreateCompanyHandler::class);
Yii::$container->set(UpdateCompanyHandler::class);
Yii::$container->set(ArchiveCompanyHandler::class);
Yii::$container->set(UnarchiveCompanyHandler::class);
Yii::$container->set(DeleteCompanyHandler::class);
Yii::$container->set(VerifyCompanyHandler::class);
Yii::$container->set(GetCompanyHandler::class);
Yii::$container->set(ListCompaniesHandler::class);
Yii::$container->set(ChangeMemberRoleHandler::class);
Yii::$container->set(RemoveCompanyMemberHandler::class);
Yii::$container->set(TransferOwnershipHandler::class);
Yii::$container->set(InviteUserHandler::class);
Yii::$container->set(AcceptInvitationHandler::class);
Yii::$container->set(CancelInvitationHandler::class);

// Диспетчер событий модуля
Yii::$container->set(ModuleEventDispatcher::class, function (Container $container) {
    $listeners = [
        CompanyCreatedEvent::class => [
            // TODO: добавить логирование и другие слушатели
        ],
        CompanyUpdatedEvent::class => [],
        CompanyArchivedEvent::class => [],
        CompanyVerifiedEvent::class => [],
        CompanyMemberAddedEvent::class => [],
        CompanyMemberRemovedEvent::class => [],
        CompanyInvitationCreatedEvent::class => [
            // TODO: добавить отправку email с приглашением
        ],
        CompanyInvitationAcceptedEvent::class => [],
        CompanyTransferOwnershipEvent::class => [],
    ];

    return new ModuleEventDispatcher($listeners);
});

$forwardEvents = [
    CompanyCreatedEvent::class,
    CompanyMemberAddedEvent::class,
    CompanyMemberRemovedEvent::class,
    CompanyArchivedEvent::class,
    CompanyVerifiedEvent::class,
    CompanyInvitationCreatedEvent::class,
    CompanyInvitationAcceptedEvent::class,
    CompanyTransferOwnershipEvent::class,
];

Yii::$container->set(CompanyEventDispatcher::class, function (Container $container) use ($forwardEvents) {
    return new DispatchingEventDecorator(
        $container->get(ModuleEventDispatcher::class),
        $container->get(GlobalEventDispatcher::class),
        $forwardEvents,
    );
});