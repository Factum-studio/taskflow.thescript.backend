<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\ProjectDtoAssembler;
use modules\projects\application\command\CreateProjectCommand;
use modules\projects\application\dto\ProjectDto;
use modules\projects\application\port\IProjectAccess;
use modules\projects\domain\entity\Project;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\ProjectType;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\Settings;
use DateTimeImmutable;
use RuntimeException;

class CreateProjectHandler
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

    public function handle(CreateProjectCommand $command): ProjectDto
    {
        if (!$this->projectAccess->canCreateProject($command->ownerId)) {
            throw new RuntimeException('You have reached the maximum number of projects');
        }

        $project = new Project(
            new ProjectId(0),
            $command->name,
            new ProjectType($command->type),
            new UserId($command->ownerId),
            new Settings($command->settings ?? []),
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );

        $savedProject = $this->projectRepository->save($project);

        return $this->projectDtoAssembler->toDto($savedProject);
    }
}