<?php

namespace modules\projects\application\handler;

use core\domain\valueObject\UserId;
use modules\projects\application\assembler\ProjectDtoAssembler;
use modules\projects\application\dto\ProjectDto;
use modules\projects\application\query\ListUserProjectsQuery;
use modules\projects\domain\repository\IProjectRepository;

class ListUserProjectsHandler
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

    /**
     * @return ProjectDto[]
     */
    public function handle(ListUserProjectsQuery $query): array
    {
        $projects = $this->projectRepository->findByUser(new UserId($query->userId));
        return $this->projectDtoAssembler->toDtoList($projects);
    }
}