<?php

declare(strict_types=1);

namespace modules\projects\application\handler;

use modules\projects\application\assembler\ProjectDtoAssembler;
use modules\projects\application\dto\ProjectDto;
use modules\projectRbac\application\port\IProjectAccessService;
use modules\projects\application\query\GetProjectQuery;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\ProjectId;
use RuntimeException;

class GetProjectHandler
{
    private IProjectRepository $projectRepository;
    private ProjectDtoAssembler $projectDtoAssembler;
    private IProjectAccessService $projectAccess;

    public function __construct(
        IProjectRepository $projectRepository,
        ProjectDtoAssembler $projectDtoAssembler,
        IProjectAccessService $projectAccess,
    ) {
        $this->projectRepository    = $projectRepository;
        $this->projectDtoAssembler  = $projectDtoAssembler;
        $this->projectAccess        = $projectAccess;
    }

    public function handle(GetProjectQuery $query): ProjectDto
    {
        $projectId = new ProjectId($query->id);
        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new RuntimeException("Project with ID {$query->id} not found");
        }

        if (!$this->projectAccess->canViewProject($query->userId, $query->id)) {
            throw new RuntimeException('You are not allowed to view this project');
        }

        return $this->projectDtoAssembler->toDto($project);
    }
}
