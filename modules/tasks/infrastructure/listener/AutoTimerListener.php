<?php

namespace modules\tasks\infrastructure\listener;

use DateTimeImmutable;
use modules\tasks\domain\entity\TimeInterval;
use modules\tasks\domain\event\TaskAssignedEvent;
use modules\tasks\domain\event\TaskSoftDeletedEvent;
use modules\tasks\domain\event\TaskStatusChangedEvent;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITaskStatusRepository;
use modules\tasks\domain\repository\ITimeIntervalRepository;
use modules\tasks\domain\valueObject\StatusId;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\TimeIntervalId;
use modules\tasks\domain\valueObject\UserId;
use Yii;

class AutoTimerListener
{
    private ITimeIntervalRepository $intervalRepository;
    private ITaskRepository $taskRepository;
    private ITaskStatusRepository $statusRepository;

    // Список системных статусов, которые считаются активными (по имени)
    private array $activeStatusNames = ['in_progress', 'review', 'testing', 'blocked'];

    public function __construct(
        ITimeIntervalRepository $intervalRepository,
        ITaskRepository $taskRepository,
        ITaskStatusRepository $statusRepository
    ) {
        $this->intervalRepository = $intervalRepository;
        $this->taskRepository = $taskRepository;
        $this->statusRepository = $statusRepository;
    }

    public function handleTaskStatusChanged(TaskStatusChangedEvent $event): void
    {
        $taskId = new TaskId($event->getAggregateId());
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            Yii::warning("Task not found in AutoTimerListener for event", 'tasks');
            return;
        }

        $oldStatus = $this->statusRepository->findById(new StatusId($event->getOldStatusId()));
        $newStatus = $this->statusRepository->findById(new StatusId($event->getNewStatusId()));

        if (!$oldStatus || !$newStatus) {
            Yii::warning("Status not found in AutoTimerListener", 'tasks');
            return;
        }

        $wasActive = in_array($oldStatus->getName(), $this->activeStatusNames, true);
        $isActive = in_array($newStatus->getName(), $this->activeStatusNames, true);
        $isDone = $newStatus->getName() === 'done'; // завершающий статус

        $userId = $task->getAssignedTo() ?? $task->getCreatedBy();

        if (!$wasActive && $isActive) {
            // Переход в активный статус - запускаем таймер, если ещё нет активного
            $existing = $this->intervalRepository->findActiveByTaskAndUser($taskId, $userId, TimeInterval::TYPE_TIMER);
            if (!$existing) {
                $interval = new TimeInterval(
                    new TimeIntervalId(0),
                    $taskId,
                    $userId,
                    new DateTimeImmutable(),
                    null,
                    null,
                    'Auto-started by status change',
                    TimeInterval::TYPE_TIMER
                );
                $this->intervalRepository->save($interval);
                Yii::info("Auto timer started for task {$taskId->getValue()} user {$userId->getValue()}", 'tasks');
            }
        } elseif ($wasActive && !$isActive && !$isDone) {
            // Переход из активного в неактивный (кроме done) - удаляем активный таймер
            $active = $this->intervalRepository->findActiveByTaskAndUser($taskId, $userId, TimeInterval::TYPE_TIMER);
            if ($active) {
                $this->intervalRepository->remove($active);
                Yii::info("Auto timer deleted for task {$taskId->getValue()} user {$userId->getValue()}", 'tasks');
            }
        } elseif ($wasActive && $isDone) {
            // Завершение задачи - останавливаем таймер
            $active = $this->intervalRepository->findActiveByTaskAndUser($taskId, $userId, TimeInterval::TYPE_TIMER);
            if ($active) {
                $active->stop(new DateTimeImmutable(), 'Task completed');
                $this->intervalRepository->save($active);
                Yii::info("Auto timer stopped for task {$taskId->getValue()} user {$userId->getValue()}", 'tasks');
            }
        }
    }

    public function handleTaskAssigned(TaskAssignedEvent $event): void
    {
        $taskId = new TaskId($event->getAggregateId());
        $task = $this->taskRepository->findById($taskId);
        if (!$task) return;

        $oldAssigneeId = $event->getOldAssigneeId();
        $newAssigneeId = $event->getNewAssigneeId();

        if ($oldAssigneeId && $newAssigneeId && $oldAssigneeId !== $newAssigneeId) {
            $oldUserId = new UserId($oldAssigneeId);
            $newUserId = new UserId($newAssigneeId);

            // Переносим активный таймер (если есть) со старого исполнителя на нового
            $active = $this->intervalRepository->findActiveByTaskAndUser($taskId, $oldUserId, TimeInterval::TYPE_TIMER);
            if ($active) {
                // Меняем владельца
                $active = new TimeInterval(
                    $active->getId(),
                    $active->getTaskId(),
                    $newUserId,
                    $active->getStartTime(),
                    $active->getEndTime(),
                    $active->getDuration(),
                    $active->getComment(),
                    $active->getType(),
                    $active->getCreatedAt(),
                    $active->getUpdatedAt()
                );
                $this->intervalRepository->save($active);
                Yii::info("Auto timer moved from user {$oldAssigneeId} to {$newAssigneeId} for task {$taskId->getValue()}", 'tasks');
            }
        }
    }

    public function handleTaskSoftDeleted(TaskSoftDeletedEvent $event): void
    {
        $taskId = new TaskId($event->getAggregateId());
        $task = $this->taskRepository->findById($taskId);
        if (!$task) return;

        $userId = $task->getAssignedTo() ?? $task->getCreatedBy();
        $active = $this->intervalRepository->findActiveByTaskAndUser($taskId, $userId, TimeInterval::TYPE_TIMER);
        if ($active) {
            $active->stop(new DateTimeImmutable(), 'Task deleted');
            $this->intervalRepository->save($active);
            Yii::info("Auto timer stopped due to task deletion for task {$taskId->getValue()}", 'tasks');
        }
    }
}