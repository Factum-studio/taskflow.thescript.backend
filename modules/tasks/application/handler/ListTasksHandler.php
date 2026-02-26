<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\application\query\ListTasksQuery;
use modules\tasks\domain\repository\ITaskRepository;

class ListTasksHandler
{
    private ITaskRepository $taskRepository;
    private TaskDtoAssembler $taskDtoAssembler;

    public function __construct(
        ITaskRepository $taskRepository,
        TaskDtoAssembler $taskDtoAssembler
    ) {
        $this->taskRepository   = $taskRepository;
        $this->taskDtoAssembler = $taskDtoAssembler;
    }

    /**
     * @return TaskDto[]
     */
    public function handle(ListTasksQuery $query): array
    {
        $criteria = $query->toCriteria();
        $tasks = $this->taskRepository->findAll($criteria);
        return $this->taskDtoAssembler->toDtoList($tasks);
    }
}