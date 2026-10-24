<?php

declare(strict_types=1);

namespace modules\projects\application\assembler;

use modules\projects\domain\entity\ProjectUser;
use modules\projects\application\dto\ProjectUserDto;
use modules\projectRbac\application\port\IProjectRoleRepository;

class ProjectUserDtoAssembler
{
    public function __construct(private IProjectRoleRepository $roleRepository)
    {
    }

    public function toDto(ProjectUser $projectUser): ProjectUserDto
    {
        $roleId = $projectUser->getRoleId();
        $role = $roleId !== null ? $this->roleRepository->findRoleById($roleId) : null;
        $roleSlug = $role !== null && $role->getProjectId() === $projectUser->getProjectId()->value()
            ? $role->getSlug()
            : $projectUser->getRole()->value();

        return new ProjectUserDto([
            'projectId'     => $projectUser->getProjectId()->value(),
            'userId'        => $projectUser->getUserId()->value(),
            'role'          => $roleSlug,
            'roleId'        => $roleId,
            'invitedBy'     => $projectUser->getInvitedBy()?->value(),
            'invitedAt'     => $projectUser->getInvitedAt()?->format('Y-m-d H:i:s'),
            'acceptedAt'    => $projectUser->getAcceptedAt()?->format('Y-m-d H:i:s'),
            'joinedAt'      => $projectUser->getJoinedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param ProjectUser[] $projectUsers
     * @return ProjectUserDto[]
     */
    public function toDtoList(array $projectUsers): array
    {
        return array_map([$this, 'toDto'], $projectUsers);
    }
}
