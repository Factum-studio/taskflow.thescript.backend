<?php

namespace modules\tasks\application\handler;

use DateTimeImmutable;
use InvalidArgumentException;
use modules\tasks\application\assembler\TimeIntervalDtoAssembler;
use modules\tasks\application\command\StopTimerCommand;
use modules\tasks\application\dto\TimeIntervalDto;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\TimerStoppedEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITimeIntervalRepository;
use modules\tasks\domain\valueObject\TimeIntervalId;
use RuntimeException;

class StopTimerHandler
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

    public function handle(StopTimerCommand $command): TimeIntervalDto
    {
        $intervalId = new TimeIntervalId($command->intervalId);
        $interval = $this->intervalRepository->findById($intervalId);
        if (!$interval) {
            throw new RuntimeException("Interval with ID {$command->intervalId} not found");
        }

        if ($interval->getUserId()->getValue() !== $command->userId) {
            throw new InvalidArgumentException("You are not allowed to stop this timer");
        }

        if ($interval->isStopped()) {
            throw new InvalidArgumentException("Timer already stopped");
        }

        $stopTime = $command->stopTime ?? new DateTimeImmutable();
        $interval->stop($stopTime, $command->comment);
        $saved = $this->intervalRepository->save($interval);
        $this->eventDispatcher->dispatch(new TimerStoppedEvent($saved));

        return $this->dtoAssembler->toDto($saved);
    }
}