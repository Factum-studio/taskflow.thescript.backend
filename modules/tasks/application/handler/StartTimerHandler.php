<?php

namespace modules\tasks\application\handler;

use DateTimeImmutable;
use InvalidArgumentException;
use modules\tasks\application\assembler\TimeIntervalDtoAssembler;
use modules\tasks\application\command\StartTimerCommand;
use modules\tasks\application\dto\TimeIntervalDto;
use modules\tasks\domain\entity\TimeInterval;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TimerStartedEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITimeIntervalRepository;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\TimeIntervalId;
use modules\tasks\domain\valueObject\UserId;
use RuntimeException;

class StartTimerHandler
{
    private ITimeIntervalRepository $intervalRepository;
    private ITaskRepository $taskRepository;
    private TimeIntervalDtoAssembler $dtoAssembler;
    private IEventDispatcher $eventDispatcher;

    public function __construct(
        ITimeIntervalRepository $intervalRepository,
        ITaskRepository $taskRepository,
        TimeIntervalDtoAssembler $dtoAssembler,
        IEventDispatcher $eventDispatcher
    ) {
        $this->intervalRepository   = $intervalRepository;
        $this->taskRepository       = $taskRepository;
        $this->dtoAssembler         = $dtoAssembler;
        $this->eventDispatcher      = $eventDispatcher;
    }

    public function handle(StartTimerCommand $command): TimeIntervalDto
    {
        $taskId = new TaskId($command->taskId);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with ID {$command->taskId} not found");
        }
        if ($task->getDeletedAt() !== null) {
            throw new InvalidArgumentException("Cannot start timer on deleted task");
        }

        $userId = new UserId($command->userId);
        $active = $this->intervalRepository->findActiveInterval($userId, $taskId);
        if ($active) {
            throw new InvalidArgumentException("User already has an active timer on this task");
        }

        $startTime = $command->startTime ?? new DateTimeImmutable();

        $interval = new TimeInterval(
            new TimeIntervalId(0),
            $taskId,
            $userId,
            $startTime,
            null,
            null,
            $command->comment
        );

        $saved = $this->intervalRepository->save($interval);
        $this->eventDispatcher->dispatch(new TimerStartedEvent($saved));

        return $this->dtoAssembler->toDto($saved);
    }
}