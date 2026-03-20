<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\ProjectUserDtoAssembler;
use modules\projects\application\command\ChangeMemberRoleCommand;
use modules\projects\application\dto\ProjectUserDto;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\UserRole;
use RuntimeException;

class ChangeMemberRoleHandler
{
    private IProjectUserRepository $projectUserRepository;
    private ProjectUserDtoAssembler $projectUserDtoAssembler;

    public function __construct(
        IProjectUserRepository $projectUserRepository,
        ProjectUserDtoAssembler $projectUserDtoAssembler
    ) {
        $this->projectUserRepository    = $projectUserRepository;
        $this->projectUserDtoAssembler  = $projectUserDtoAssembler;
    }

    public function handle(ChangeMemberRoleCommand $command): ProjectUserDto
    {
        $projectId = new ProjectId($command->projectId);
        $userId = new UserId($command->userId);

        $projectUser = $this->projectUserRepository->find($projectId, $userId);
        if ($projectUser === null) {
            throw new RuntimeException("User is not a member of this project");
        }

        // Проверка прав: админ проекта может менять роли
        // TODO: проверить, что $command->changedBy является админом

        $projectUser->changeRole(new UserRole($command->newRole));

        $this->projectUserRepository->save($projectUser);

        return $this->projectUserDtoAssembler->toDto($projectUser);
    }
}