<?php

namespace modules\tasks\application\handler;

use Exception;
use modules\tasks\application\assembler\TaskTimeSummaryDtoAssembler;
use modules\tasks\application\dto\TaskTimeSummaryDto;
use modules\tasks\application\query\GetTaskTimeSummaryQuery;
use modules\tasks\application\service\BusyChartService;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITimeIntervalRepository;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;
use RuntimeException;

class GetTaskTimeSummaryHandler
{
    private ITimeIntervalRepository $timeIntervalRepository;
    private ITaskRepository $taskRepository;
    private BusyChartService $busyChartService;
    private TaskTimeSummaryDtoAssembler $dtoAssembler;
    public function __construct(
        ITimeIntervalRepository $timeIntervalRepository,
        ITaskRepository $taskRepository,
        BusyChartService $busyChartService,
        TaskTimeSummaryDtoAssembler $dtoAssembler
    ) {
        $this->timeIntervalRepository   = $timeIntervalRepository;
        $this->taskRepository           = $taskRepository;
        $this->busyChartService         = $busyChartService;
        $this->dtoAssembler             = $dtoAssembler;
    }

    /**
     * @throws Exception
     */
    public function handle(GetTaskTimeSummaryQuery $query): TaskTimeSummaryDto
    {
        $taskId = new TaskId($query->taskId);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with ID {$query->taskId} not found.");
        }

        $userId = $query->userId ? new UserId($query->userId) : null;
        $intervals = $this->timeIntervalRepository->findByTaskAndUser(
            $taskId,
            $userId,
            $query->from,
            $query->to
        );

        $blocks = $this->busyChartService->build($intervals, $query->granularity, $query->mode);

        $totalDuration = 0;
        foreach ($intervals as $interval) {
            if ($interval->isStopped()) {
                $totalDuration += $interval->getDurationSeconds();
            }
        }

        $summaryData = [
            'taskId' => $task->getId()->getValue(),
            'taskTitle' => $task->getTitle()->getValue(),
            'blocks' => $blocks,
            'totalDuration' => $totalDuration,
        ];

        return $this->dtoAssembler->toDto($summaryData);
    }
}