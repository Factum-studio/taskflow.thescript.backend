<?php

namespace modules\projects\application\handler;

use modules\projects\application\command\RemoveProjectMemberCommand;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\event\IEventDispatcher;
use modules\projects\domain\event\ProjectMemberRemovedEvent;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use RuntimeException;

class RemoveProjectMemberHandler
{
    private IProjectUserRepository $projectUserRepository;
    private IProjectAccess $projectAccess;
    private IEventDispatcher $eventDispatcher;

    public function __construct(
        IProjectUserRepository $projectUserRepository,
        IProjectAccess $projectAccess,
        IEventDispatcher $eventDispatcher
    ) {
        $this->projectUserRepository    = $projectUserRepository;
        $this->projectAccess            = $projectAccess;
        $this->eventDispatcher          = $eventDispatcher;
    }

    public function handle(RemoveProjectMemberCommand $command): void
    {
        $projectId = new ProjectId($command->projectId);
        $userId = new UserId($command->userId);

        if (!$this->projectAccess->canRemoveUser($command->removedBy, $command->userId, $command->projectId)) {
            throw new RuntimeException('You are not allowed to remove this user from the project');
        }

        $projectUser = $this->projectUserRepository->find($projectId, $userId);
        if ($projectUser === null) {
            throw new RuntimeException("User is not a member of this project");
        }

        $this->projectUserRepository->remove($projectUser);

        $this->eventDispatcher->dispatch(new ProjectMemberRemovedEvent($projectId, $userId, $command->removedBy));
    }
}