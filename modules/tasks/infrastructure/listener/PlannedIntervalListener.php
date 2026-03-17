<?php

namespace modules\tasks\infrastructure\listener;

use DateTimeImmutable;
use modules\tasks\domain\entity\TimeInterval;
use modules\tasks\domain\event\TaskCreatedEvent;
use modules\tasks\domain\event\TaskUpdatedEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITimeIntervalRepository;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\TimeIntervalId;
use modules\tasks\domain\valueObject\UserId;
use Yii;

class PlannedIntervalListener
{
    private ITimeIntervalRepository $intervalRepository;
    private ITaskRepository $taskRepository;

    public function __construct(
        ITimeIntervalRepository $intervalRepository,
        ITaskRepository $taskRepository
    ) {
        $this->intervalRepository = $intervalRepository;
        $this->taskRepository = $taskRepository;
    }

    public function handleTaskCreated(TaskCreatedEvent $event): void
    {
        $taskId = new TaskId($event->getAggregateId());
        $task = $this->taskRepository->findById($taskId);
        if (!$task) return;

        $this->syncPlannedInterval($task);
    }

    public function handleTaskUpdated(TaskUpdatedEvent $event): void
    {
        $changed = $event->getChangedFields();
        // Если изменились planned-поля или исполнитель, синхронизируем плановый интервал
        if (array_intersect_key(['plannedStart' => 1, 'plannedEnd' => 1, 'assignedTo' => 1], $changed)) {
            $taskId = new TaskId($event->getAggregateId());
            $task = $this->taskRepository->findById($taskId);
            if (!$task) return;

            $this->syncPlannedInterval($task);
        }
    }

    private function syncPlannedInterval(\modules\tasks\domain\entity\Task $task): void
    {
        $taskId = $task->getId();
        $plannedStart = $task->getPlannedStart();
        $plannedEnd = $task->getPlannedEnd();
        $userId = $task->getAssignedTo() ?? $task->getCreatedBy();

        $existingIntervals = $this->intervalRepository->findByTask($taskId, TimeInterval::TYPE_PLAN);
        $existing = $existingIntervals ? $existingIntervals[0] : null;

        // Если нет plannedStart — интервал не нужен, удаляем существующий (если есть)
        if ($plannedStart === null) {
            if ($existing) {
                $this->intervalRepository->remove($existing);
                Yii::info("Planned interval removed for task {$taskId->getValue()} (plannedStart null)", 'tasks');
            }
            return;
        }

        // plannedStart !== null
        if ($existing) {
            // Обновляем существующий интервал
            $existing = new TimeInterval(
                $existing->getId(),
                $taskId,
                $userId,
                $plannedStart,
                $plannedEnd,
                $existing->getDuration(),
                $existing->getComment(),
                TimeInterval::TYPE_PLAN,
                $existing->getCreatedAt(),
                new DateTimeImmutable()
            );
            $this->intervalRepository->save($existing);
            Yii::info("Planned interval updated for task {$taskId->getValue()}", 'tasks');
        } else {
            // Создаём новый
            $interval = new TimeInterval(
                new TimeIntervalId(0),
                $taskId,
                $userId,
                $plannedStart,
                $plannedEnd,
                null,
                null,
                TimeInterval::TYPE_PLAN
            );
            $this->intervalRepository->save($interval);
            Yii::info("Planned interval created for task {$taskId->getValue()}", 'tasks');
        }
    }
}