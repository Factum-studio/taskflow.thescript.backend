<?php

declare(strict_types=1);

namespace modules\projects\application\handler;

use core\application\port\IUserRepository;
use core\domain\valueObject\UserId;
use modules\projects\application\assembler\ProjectUserDtoAssembler;
use modules\projects\application\command\AddProjectMemberCommand;
use modules\projects\application\dto\ProjectUserDto;
use modules\projectRbac\application\port\IProjectAccessService;
use modules\projectRbac\application\port\IProjectRoleRepository;
use modules\projectRbac\application\service\ProjectRoleService;
use modules\projects\domain\entity\ProjectUser;
use modules\projects\domain\event\IEventDispatcher;
use modules\projects\domain\event\ProjectMemberAddedEvent;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserRole;
use RuntimeException;

class AddProjectMemberHandler
{
    private IProjectUserRepository $projectUserRepository;
    private IProjectRepository $projectRepository;
    private ProjectUserDtoAssembler $projectUserDtoAssembler;
    private IProjectAccessService $projectAccess;
    private IUserRepository $userRepository;
    private IEventDispatcher $eventDispatcher;
    private IProjectRoleRepository $roleRepository;
    private ProjectRoleService $roleService;

    public function __construct(
        IProjectUserRepository $projectUserRepository,
        IProjectRepository $projectRepository,
        ProjectUserDtoAssembler $projectUserDtoAssembler,
        IProjectAccessService $projectAccess,
        IUserRepository $userRepository,
        IEventDispatcher $eventDispatcher,
        IProjectRoleRepository $roleRepository,
        ProjectRoleService $roleService,
    ) {
        $this->projectUserRepository    = $projectUserRepository;
        $this->projectRepository        = $projectRepository;
        $this->projectUserDtoAssembler  = $projectUserDtoAssembler;
        $this->projectAccess            = $projectAccess;
        $this->userRepository           = $userRepository;
        $this->eventDispatcher          = $eventDispatcher;
        $this->roleRepository           = $roleRepository;
        $this->roleService              = $roleService;
    }

    public function handle(AddProjectMemberCommand $command): ProjectUserDto
    {
        $projectId = new ProjectId($command->projectId);
        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new RuntimeException("Project with ID {$command->projectId} not found");
        }

        if (!$this->projectAccess->canAddMember($command->addedBy, $command->userId, $command->projectId)) {
            throw new RuntimeException('You are not allowed to add users to this project');
        }

        $user = $this->userRepository->findById(new UserId($command->userId));

        $existing = $this->projectUserRepository->find($projectId, new UserId($command->userId));
        if ($existing !== null) {
            throw new RuntimeException("User is already a member of this project");
        }

        $targetRole = null;
        foreach ($this->roleRepository->findRolesByProject($command->projectId) as $projectRole) {
            if ($projectRole->getSlug() === $command->role) {
                $targetRole = $projectRole;
                break;
            }
        }
        if ($targetRole === null) {
            throw new RuntimeException('Role does not exist in this project');
        }
        if (!$this->roleService->canAssignRole($command->addedBy, $command->projectId, $targetRole)) {
            throw new RuntimeException('You cannot assign this role based on the project role hierarchy');
        }

        $projectUser = new ProjectUser(
            $projectId,
            new UserId($command->userId),
            new UserRole($command->role === 'admin' ? UserRole::ADMIN : UserRole::MEMBER),
            $targetRole->getId(),
            new UserId($command->addedBy),
            new \DateTimeImmutable(),
            null,
            new \DateTimeImmutable(),
        );

        $this->projectUserRepository->save($projectUser);

        $this->eventDispatcher->dispatch(new ProjectMemberAddedEvent($projectUser, $command->addedBy));

        return $this->projectUserDtoAssembler->toDto($projectUser);
    }
}
