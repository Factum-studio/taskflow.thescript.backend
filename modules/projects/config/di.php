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