<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\ProjectDtoAssembler;
use modules\projects\application\dto\ProjectDto;
use modules\projects\application\query\GetProjectQuery;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\ProjectId;
use RuntimeException;

class GetProjectHandler
{
    private IProjectRepository $projectRepository;
    private ProjectDtoAssembler $projectDtoAssembler;

    public function __construct(
        IProjectRepository $projectRepository,
        ProjectDtoAssembler $projectDtoAssembler
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectDtoAssembler = $projectDtoAssembler;
    }

    public function handle(GetProjectQuery $query): ProjectDto
    {
        $projectId = new ProjectId($query->id);
        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new RuntimeException("Project with ID {$query->id} not found");
        }

        return $this->projectDtoAssembler->toDto($project);
    }
}