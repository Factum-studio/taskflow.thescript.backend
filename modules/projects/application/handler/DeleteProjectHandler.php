<?php

declare(strict_types=1);

namespace modules\projects\application\handler;

use modules\projects\application\command\DeleteProjectCommand;
use modules\projectRbac\application\port\IProjectAccessService;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\ProjectId;
use RuntimeException;

class DeleteProjectHandler
{
    private IProjectRepository $projectRepository;
    private IProjectAccessService $projectAccess;

    public function __construct(
        IProjectRepository $projectRepository,
        IProjectAccessService $projectAccess,
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
