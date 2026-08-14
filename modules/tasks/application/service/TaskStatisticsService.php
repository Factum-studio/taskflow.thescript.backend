<?php
namespace modules\tasks\application\service;

use core\application\port\ITaskStatisticsService;
use modules\tasks\domain\repository\ITaskRepository;

class TaskStatisticsService implements ITaskStatisticsService
{
    public function __construct(private ITaskRepository $taskRepository) {}

    public function getTotalTasks(): int
    {
        return $this->taskRepository->countAll();
    }
}