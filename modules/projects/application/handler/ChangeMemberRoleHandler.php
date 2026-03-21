<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\ProjectUserDtoAssembler;
use modules\projects\application\command\ChangeMemberRoleCommand;
use modules\projects\application\dto\ProjectUserDto;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\UserRole;
use RuntimeException;

class ChangeMemberRoleHandler
{
    private IProjectUserRepository $projectUserRepository;
    private ProjectUserDtoAssembler $projectUserDtoAssembler;
    private IProjectAccess $projectAccess;

    public function __construct(
        IProjectUserRepository $projectUserRepository,
        ProjectUserDtoAssembler $projectUserDtoAssembler,
        IProjectAccess $projectAccess
    ) {
        $this->projectUserRepository    = $projectUserRepository;
        $this->projectUserDtoAssembler  = $projectUserDtoAssembler;
        $this->projectAccess            = $projectAccess;
    }

    public function handle(ChangeMemberRoleCommand $command): ProjectUserDto
    {
        $projectId = new ProjectId($command->projectId);
        $userId = new UserId($command->userId);

        if (!$this->projectAccess->canManageUser($command->changedBy, $command->userId, $command->projectId)) {
            throw new RuntimeException('You are not allowed to change this user\'s role');
        }

        $projectUser = $this->projectUserRepository->find($projectId, $userId);
        if ($projectUser === null) {
            throw new RuntimeException("User is not a member of this project");
        }

        $projectUser->changeRole(new UserRole($command->newRole));
        $this->projectUserRepository->save($projectUser);

        return $this->projectUserDtoAssembler->toDto($projectUser);
    }
}