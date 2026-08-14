<?php

namespace modules\projects\application\handler;

use modules\projects\application\command\DeleteProjectCommand;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use RuntimeException;

class DeleteProjectHandler
{
    private IProjectRepository $projectRepository;
    private IProjectAccess $projectAccess;

    public function __construct(
        IProjectRepository $projectRepository,
        IProjectAccess $projectAccess
    ) {
        $this->projectRepository    = $projectRepository;
        $this->projectAccess        = $projectAccess;
    }

    public function handle(DeleteProjectCommand $command): void
    {
        $projectId = new ProjectId($command->id);
        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new RuntimeException("Project with ID {$command->id} not found");
        }

        if (!$this->projectAccess->canDeleteProject($command->deletedBy, $command->id)) {
            throw new RuntimeException('You are not allowed to delete this project');
        }

        $this->projectRepository->remove($project);
    }
}