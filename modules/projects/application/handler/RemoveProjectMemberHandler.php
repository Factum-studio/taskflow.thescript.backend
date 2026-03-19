<?php

namespace modules\projects\application\handler;

use modules\projects\application\command\RemoveProjectMemberCommand;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use RuntimeException;

class RemoveProjectMemberHandler
{
    private IProjectUserRepository $projectUserRepository;

    public function __construct(IProjectUserRepository $projectUserRepository)
    {
        $this->projectUserRepository = $projectUserRepository;
    }

    public function handle(RemoveProjectMemberCommand $command): void
    {
        $projectId = new ProjectId($command->projectId);
        $userId = new UserId($command->userId);

        $projectUser = $this->projectUserRepository->find($projectId, $userId);
        if ($projectUser === null) {
            throw new RuntimeException("User is not a member of this project");
        }

        // Проверка прав: админ проекта может удалять участников
        // TODO: проверить, что $command->removedBy является админом

        $this->projectUserRepository->remove($projectUser);
    }
}