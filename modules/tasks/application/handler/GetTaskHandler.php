<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\application\query\GetTaskQuery;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class GetTaskHandler
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

    public function handle(GetTaskQuery $query): TaskDto
    {
        $taskId = new TaskId($query->id);
        $task = $this->taskRepository->findById($taskId);

        if ($task === null) {
            throw new RuntimeException("Task with ID {$query->id} not found");
        }

        return $this->taskDtoAssembler->toDto($task);
    }
}