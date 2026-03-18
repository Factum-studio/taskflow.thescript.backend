<?php

namespace modules\projects\application\handler;

use modules\projects\application\assembler\ProjectUserDtoAssembler;
use modules\projects\application\command\AddProjectMemberCommand;
use modules\projects\application\dto\ProjectUserDto;
use modules\projects\domain\entity\ProjectUser;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\UserRole;
use RuntimeException;

class AddProjectMemberHandler
{
    private IProjectUserRepository $projectUserRepository;
    private IProjectRepository $projectRepository;
    private ProjectUserDtoAssembler $projectUserDtoAssembler;

    public function __construct(
        IProjectUserRepository $projectUserRepository,
        IProjectRepository $projectRepository,
        ProjectUserDtoAssembler $projectUserDtoAssembler
    ) {
        $this->projectUserRepository = $projectUserRepository;
        $this->projectRepository = $projectRepository;
        $this->projectUserDtoAssembler = $projectUserDtoAssembler;
    }

    public function handle(AddProjectMemberCommand $command): ProjectUserDto
    {
        $projectId = new ProjectId($command->projectId);
        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new RuntimeException("Project with ID {$command->projectId} not found");
        }

        // Проверка прав: только админ проекта может добавлять участников
        // TODO: проверить, что $command->addedBy является админом

        // Проверяем, не участник ли уже
        $existing = $this->projectUserRepository->find($projectId, new UserId($command->userId));
        if ($existing !== null) {
            throw new RuntimeException("User is already a member of this project");
        }

        $projectUser = new ProjectUser(
            $projectId,
            new UserId($command->userId),
            new UserRole($command->role),
            new UserId($command->addedBy),
            new \DateTimeImmutable(), // invitedAt
            null, // acceptedAt (пока без подтверждения)
            new \DateTimeImmutable() // joinedAt
        );

        $this->projectUserRepository->save($projectUser);

        return $this->projectUserDtoAssembler->toDto($projectUser);
    }
}