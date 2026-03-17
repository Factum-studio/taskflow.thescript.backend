<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\tasks\application\assembler\TimeIntervalDtoAssembler;
use modules\tasks\application\command\LogManualIntervalCommand;
use modules\tasks\application\dto\TimeIntervalDto;
use modules\tasks\domain\entity\TimeInterval;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\IntervalLoggedEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITimeIntervalRepository;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\TimeIntervalId;
use modules\tasks\domain\valueObject\UserId;
use RuntimeException;

class LogManualIntervalHandler
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

    public function handle(LogManualIntervalCommand $command): TimeIntervalDto
    {
        $taskId = new TaskId($command->taskId);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with ID {$command->taskId} not found");
        }

        $userId = new UserId($command->userId);
        $start = $command->startTime;
        $end = $command->endTime;

        if ($start >= $end) {
            throw new InvalidArgumentException("Start time must be before end time");
        }

        $interval = new TimeInterval(
            new TimeIntervalId(0),
            $taskId,
            $userId,
            $start
        );
        $interval->stop($end, $command->comment);

        $saved = $this->intervalRepository->save($interval);
        $this->eventDispatcher->dispatch(new IntervalLoggedEvent($saved));

        return $this->dtoAssembler->toDto($saved);
    }
}