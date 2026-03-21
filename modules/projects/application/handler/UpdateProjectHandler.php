<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\ProjectDtoAssembler;
use modules\projects\application\command\UpdateProjectCommand;
use modules\projects\application\dto\ProjectDto;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use RuntimeException;

class UpdateProjectHandler
{
    private IProjectRepository $projectRepository;
    private ProjectDtoAssembler $projectDtoAssembler;
    private IProjectAccess $projectAccess;

    public function __construct(
        IProjectRepository $projectRepository,
        ProjectDtoAssembler $projectDtoAssembler,
        IProjectAccess $projectAccess
    ) {
        $this->projectRepository    = $projectRepository;
        $this->projectDtoAssembler  = $projectDtoAssembler;
        $this->projectAccess        = $projectAccess;
    }

    public function handle(UpdateProjectCommand $command): ProjectDto
    {
        $projectId = new ProjectId($command->id);
        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new RuntimeException("Project with ID {$command->id} not found");
        }

        if (!$this->projectAccess->canManageProject($command->updatedBy, $command->id)) {
            throw new RuntimeException('You are not allowed to update this project');
        }

        if ($command->name !== null) {
            $project->rename($command->name);
        }

        if ($command->settings !== null) {
            $project->changeSettings($command->settings);
        }

        $savedProject = $this->projectRepository->save($project);

        return $this->projectDtoAssembler->toDto($savedProject);
    }
}