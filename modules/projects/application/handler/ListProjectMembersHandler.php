<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\ProjectUserDtoAssembler;
use modules\projects\application\dto\ProjectUserDto;
use modules\projects\application\port\IProjectAccess;
use modules\projects\application\query\ListProjectMembersQuery;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use RuntimeException;

class ListProjectMembersHandler
{
    private IProjectUserRepository $projectUserRepository;
    private ProjectUserDtoAssembler $projectUserDtoAssembler;
    private IProjectAccess $projectAccess;

    public function __construct(
        IProjectUserRepository $projectUserRepository,
        ProjectUserDtoAssembler $projectUserDtoAssembler,
        IProjectAccess $projectAccess
    ) {
        $this->projectUserRepository    = $projectUserRepository;
        $this->projectUserDtoAssembler  = $projectUserDtoAssembler;
        $this->projectAccess            = $projectAccess;
    }

    /**
     * @return ProjectUserDto[]
     */
    public function handle(ListProjectMembersQuery $query): array
    {
        if (!$this->projectAccess->canViewProject($query->userId, $query->projectId)) {
            throw new RuntimeException('You are not allowed to view members of this project');
        }

        $members = $this->projectUserRepository->findByProject(new ProjectId($query->projectId));
        return $this->projectUserDtoAssembler->toDtoList($members);
    }
}