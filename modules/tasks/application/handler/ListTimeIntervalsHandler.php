<?php

namespace modules\tasks\application\handler;

use DateTimeImmutable;
use modules\tasks\application\assembler\TimeIntervalDtoAssembler;
use modules\tasks\application\query\ListTimeIntervalsQuery;
use modules\tasks\domain\repository\ITimeIntervalRepository;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;

class ListTimeIntervalsHandler
{
    private ITimeIntervalRepository $timeIntervalRepository;
    private TimeIntervalDtoAssembler $dtoAssembler;

    public function __construct(
        ITimeIntervalRepository $timeIntervalRepository,
        TimeIntervalDtoAssembler $dtoAssembler
    ) {
        $this->timeIntervalRepository   = $timeIntervalRepository;
        $this->dtoAssembler             = $dtoAssembler;
    }

    public function handle(ListTimeIntervalsQuery $query): array
    {
        if ($query->activeOnly) {
            $userId = $query->userId ? new UserId($query->userId) : null;
            $taskId = $query->taskId ? new TaskId($query->taskId) : null;
            if (!$userId) {
                return [];
            }
            $intervals = $this->timeIntervalRepository->findAllActive($userId, $taskId);
            return $this->dtoAssembler->toDtoList($intervals);
        }

        if ($query->taskId && $query->userId) {
            $intervals = $this->timeIntervalRepository->findByTaskAndUser(
                new TaskId($query->taskId),
                new UserId($query->userId),
                $query->from,
                $query->to
            );
        } elseif ($query->userId) {
            $intervals = $this->timeIntervalRepository->findByUser(
                new UserId($query->userId),
                $query->from ?? new DateTimeImmutable('-1 month'),
                $query->to ?? new DateTimeImmutable()
            );
        } else {
            return [];
        }

        return $this->dtoAssembler->toDtoList($intervals);
    }
}