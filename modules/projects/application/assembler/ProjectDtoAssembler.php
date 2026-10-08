<?php

namespace modules\projects\application\assembler;

use modules\projects\domain\entity\Project;
use modules\projects\application\dto\ProjectDto;

class ProjectDtoAssembler
{
    public function toDto(Project $project): ProjectDto
    {
        return new ProjectDto([
            'id'        => $project->getId()->value(),
            'name'      => $project->getName(),
            'type'      => $project->getType()->value(),
            'ownerId'   => $project->getOwnerId()->value(),
            'settings'  => $project->getSettings()->toArray(),
            'createdAt' => $project->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $project->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Project[] $projects
     * @return ProjectDto[]
     */
    public function toDtoList(array $projects): array
    {
        return array_map([$this, 'toDto'], $projects);
    }
}