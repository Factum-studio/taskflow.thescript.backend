<?php

declare(strict_types=1);

namespace modules\projects\application\handler;

use core\domain\valueObject\UserId;
use modules\projects\application\assembler\ProjectUserDtoAssembler;
use modules\projects\application\command\ChangeMemberRoleCommand;
use modules\projects\application\dto\ProjectUserDto;
use modules\projectRbac\application\port\IProjectAccessService;
use modules\projectRbac\application\port\IProjectRoleRepository;
use modules\projectRbac\application\service\ProjectRoleService;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserRole;
use RuntimeException;

class ChangeMemberRoleHandler
{
    private IProjectUserRepository $projectUserRepository;
    private ProjectUserDtoAssembler $projectUserDtoAssembler;
    private IProjectAccessService $projectAccess;
    private IProjectRoleRepository $roleRepository;
    private ProjectRoleService $roleService;

    public function __construct(
        IProjectUserRepository $projectUserRepository,
        ProjectUserDtoAssembler $projectUserDtoAssembler,
        IProjectAccessService $projectAccess,
        IProjectRoleRepository $roleRepository,
        ProjectRoleService $roleService,
    ) {
        $this->projectUserRepository    = $projectUserRepository;
        $this->projectUserDtoAssembler  = $projectUserDtoAssembler;
        $this->projectAccess            = $projectAccess;
        $this->roleRepository           = $roleRepository;
        $this->roleService              = $roleService;
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

        $targetRole = null;
        foreach ($this->roleRepository->findRolesByProject($command->projectId) as $role) {
            if ($role->getSlug() === $command->newRole) {
                $targetRole = $role;
                break;
            }
        }
        if ($targetRole === null || !$this->roleService->canChangeUserRole($command->changedBy, $command->projectId, $command->userId, $targetRole->getId())) {
            throw new RuntimeException('You cannot assign this role based on the project role hierarchy');
        }
        $projectUser->changeRole(new UserRole($command->newRole === 'admin' ? UserRole::ADMIN : UserRole::MEMBER));
        $projectUser->changeRoleId($targetRole->getId());
        $this->projectUserRepository->save($projectUser);

        return $this->projectUserDtoAssembler->toDto($projectUser);
    }
}
