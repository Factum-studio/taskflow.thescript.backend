<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\ProjectUserDtoAssembler;
use modules\projects\application\dto\ProjectUserDto;
use modules\projects\application\query\ListProjectMembersQuery;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;

class ListProjectMembersHandler
{
    private IProjectUserRepository $projectUserRepository;
    private ProjectUserDtoAssembler $projectUserDtoAssembler;

    public function __construct(
        IProjectUserRepository $projectUserRepository,
        ProjectUserDtoAssembler $projectUserDtoAssembler
    ) {
        $this->projectUserRepository = $projectUserRepository;
        $this->projectUserDtoAssembler = $projectUserDtoAssembler;
    }

    /**
     * @return ProjectUserDto[]
     */
    public function handle(ListProjectMembersQuery $query): array
    {
        $members = $this->projectUserRepository->findByProject(new ProjectId($query->projectId));
        return $this->projectUserDtoAssembler->toDtoList($members);
    }
}