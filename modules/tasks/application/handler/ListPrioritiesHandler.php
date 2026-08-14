<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskPriorityDtoAssembler;
use modules\tasks\application\dto\TaskPriorityDto;
use modules\tasks\application\query\ListPrioritiesQuery;
use modules\tasks\domain\repository\ITaskPriorityRepository;

class ListPrioritiesHandler
{
    private ITaskPriorityRepository $priorityRepository;
    private TaskPriorityDtoAssembler $dtoAssembler;

    public function __construct(
        ITaskPriorityRepository $priorityRepository,
        TaskPriorityDtoAssembler $dtoAssembler
    ) {
        $this->priorityRepository   = $priorityRepository;
        $this->dtoAssembler         = $dtoAssembler;
    }

    /**
     * @return TaskPriorityDto[]
     */
    public function handle(ListPrioritiesQuery $query): array
    {
        $priorities = $this->priorityRepository->findAll();
        return $this->dtoAssembler->toDtoList($priorities);
    }
}