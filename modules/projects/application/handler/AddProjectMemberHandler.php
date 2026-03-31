<?php

namespace modules\projects\application\handler;

use core\application\port\IUserRepository;
use core\domain\valueObject\Identify;
use core\domain\valueObject\JwtToken;
use modules\projects\application\assembler\ProjectUserDtoAssembler;
use modules\projects\application\command\AddProjectMemberCommand;
use modules\projects\application\dto\ProjectUserDto;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\entity\ProjectUser;
use modules\projects\domain\event\IEventDispatcher;
use modules\projects\domain\event\ProjectMemberAddedEvent;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\UserRole;
use RuntimeException;

class AddProjectMemberHandler
{
    private IProjectUserRepository $projectUserRepository;
    private IProjectRepository $projectRepository;
    private ProjectUserDtoAssembler $projectUserDtoAssembler;
    private IProjectAccess $projectAccess;
    private IUserRepository $userRepository;
    private IEventDispatcher $eventDispatcher;

    public function __construct(
        IProjectUserRepository $projectUserRepository,
        IProjectRepository $projectRepository,
        ProjectUserDtoAssembler $projectUserDtoAssembler,
        IProjectAccess $projectAccess,
        IUserRepository $userRepository,
        IEventDispatcher $eventDispatcher
    ) {
        $this->projectUserRepository    = $projectUserRepository;
        $this->projectRepository        = $projectRepository;
        $this->projectUserDtoAssembler  = $projectUserDtoAssembler;
        $this->projectAccess            = $projectAccess;
        $this->userRepository           = $userRepository;
        $this->eventDispatcher          = $eventDispatcher;
    }

    public function handle(AddProjectMemberCommand $command): ProjectUserDto
    {
        $projectId = new ProjectId($command->projectId);
        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new RuntimeException("Project with ID {$command->projectId} not found");
        }

        if (!$this->projectAccess->canInviteUser($command->addedBy, $command->projectId)) {
            throw new RuntimeException('You are not allowed to invite users to this project');
        }

        $jwtToken = new JwtToken($command->jwtToken);
        $userId = Identify::fromString((string)$command->userId);
        $user = $this->userRepository->findById($userId, $jwtToken);
        if ($user === null) {
            throw new RuntimeException("User with ID {$command->userId} not found");
        }

        $existing = $this->projectUserRepository->find($projectId, new UserId($command->userId));
        if ($existing !== null) {
            throw new RuntimeException("User is already a member of this project");
        }

        $projectUser = new ProjectUser(
            $projectId,
            new UserId($command->userId),
            new UserRole($command->role),
            new UserId($command->addedBy),
            new \DateTimeImmutable(),
            null,
            new \DateTimeImmutable()
        );

        $this->projectUserRepository->save($projectUser);

        $this->eventDispatcher->dispatch(new ProjectMemberAddedEvent($projectUser, $command->addedBy));

        return $this->projectUserDtoAssembler->toDto($projectUser);
    }
}