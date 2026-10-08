<?php

namespace modules\projects\application\assembler;

use modules\projects\domain\entity\ProjectUser;
use modules\projects\application\dto\ProjectUserDto;

class ProjectUserDtoAssembler
{
    public function toDto(ProjectUser $projectUser): ProjectUserDto
    {
        return new ProjectUserDto([
            'projectId'     => $projectUser->getProjectId()->value(),
            'userId'        => $projectUser->getUserId()->value(),
            'role'          => $projectUser->getRole()->value(),
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