<?php

use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IBoardRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\infrastructure\access\ProjectAccess;
use modules\projects\infrastructure\repository\DbProjectRepository;
use modules\projects\infrastructure\repository\DbBoardRepository;
use modules\projects\infrastructure\repository\DbProjectUserRepository;
use modules\projects\application\assembler\ProjectDtoAssembler;
use modules\projects\application\assembler\BoardDtoAssembler;
use modules\projects\application\assembler\ProjectUserDtoAssembler;
use modules\projects\application\handler\CreateProjectHandler;
use modules\projects\application\handler\UpdateProjectHandler;
use modules\projects\application\handler\DeleteProjectHandler;
use modules\projects\application\handler\CreateBoardHandler;
use modules\projects\application\handler\UpdateBoardHandler;
use modules\projects\application\handler\DeleteBoardHandler;
use modules\projects\application\handler\AddProjectMemberHandler;
use modules\projects\application\handler\RemoveProjectMemberHandler;
use modules\projects\application\handler\ChangeMemberRoleHandler;
use modules\projects\application\handler\GetProjectHandler;
use modules\projects\application\handler\ListUserProjectsHandler;
use modules\projects\application\handler\GetBoardHandler;
use modules\projects\application\handler\ListProjectBoardsHandler;
use modules\projects\application\handler\ListProjectMembersHandler;
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

//Handlers
Yii::$container->set(CreateProjectHandler::class, function (Container $container) {
    return new CreateProjectHandler(
        $container->get(IProjectRepository::class),
        $container->get(ProjectDtoAssembler::class)
    );
});

Yii::$container->set(UpdateProjectHandler::class, function (Container $container) {
    return new UpdateProjectHandler(
        $container->get(IProjectRepository::class),
        $container->get(ProjectDtoAssembler::class)
    );
});

Yii::$container->set(DeleteProjectHandler::class, function (Container $container) {
    return new DeleteProjectHandler(
        $container->get(IProjectRepository::class)
    );
});

Yii::$container->set(CreateBoardHandler::class, function (Container $container) {
    return new CreateBoardHandler(
        $container->get(IBoardRepository::class),
        $container->get(IProjectRepository::class),
        $container->get(BoardDtoAssembler::class)
    );
});

Yii::$container->set(UpdateBoardHandler::class, function (Container $container) {
    return new UpdateBoardHandler(
        $container->get(IBoardRepository::class),
        $container->get(BoardDtoAssembler::class)
    );
});

Yii::$container->set(DeleteBoardHandler::class, function (Container $container) {
    return new DeleteBoardHandler(
        $container->get(IBoardRepository::class)
    );
});

Yii::$container->set(AddProjectMemberHandler::class, function (Container $container) {
    return new AddProjectMemberHandler(
        $container->get(IProjectUserRepository::class),
        $container->get(IProjectRepository::class),
        $container->get(ProjectUserDtoAssembler::class)
    );
});

Yii::$container->set(RemoveProjectMemberHandler::class, function (Container $container) {
    return new RemoveProjectMemberHandler(
        $container->get(IProjectUserRepository::class)
    );
});

Yii::$container->set(ChangeMemberRoleHandler::class, function (Container $container) {
    return new ChangeMemberRoleHandler(
        $container->get(IProjectUserRepository::class),
        $container->get(ProjectUserDtoAssembler::class)
    );
});

Yii::$container->set(GetProjectHandler::class, function (Container $container) {
    return new GetProjectHandler(
        $container->get(IProjectRepository::class),
        $container->get(ProjectDtoAssembler::class)
    );
});

Yii::$container->set(ListUserProjectsHandler::class, function (Container $container) {
    return new ListUserProjectsHandler(
        $container->get(IProjectRepository::class),
        $container->get(ProjectDtoAssembler::class)
    );
});

Yii::$container->set(GetBoardHandler::class, function (Container $container) {
    return new GetBoardHandler(
        $container->get(IBoardRepository::class),
        $container->get(BoardDtoAssembler::class)
    );
});

Yii::$container->set(ListProjectBoardsHandler::class, function (Container $container) {
    return new ListProjectBoardsHandler(
        $container->get(IBoardRepository::class),
        $container->get(BoardDtoAssembler::class)
    );
});

Yii::$container->set(ListProjectMembersHandler::class, function (Container $container) {
    return new ListProjectMembersHandler(
        $container->get(IProjectUserRepository::class),
        $container->get(ProjectUserDtoAssembler::class)
    );
});