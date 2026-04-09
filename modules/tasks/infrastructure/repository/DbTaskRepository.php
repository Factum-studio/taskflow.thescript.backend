<?php

namespace modules\tasks\infrastructure\repository;

use modules\tasks\domain\entity\Task;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\ColumnId;
use modules\tasks\domain\valueObject\PriorityId;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\Title;
use modules\tasks\domain\valueObject\UserId;
use modules\tasks\infrastructure\persistence\TaskAR;
use DateTimeImmutable;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use yii\db\Exception;
use yii\db\StaleObjectException;

class DbTaskRepository implements ITaskRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws Exception
     */
    public function save(Task $task): Task
    {
        $ar = $this->findARById($task->getId()) ?? new TaskAR();
        $this->mapEntityToAR($task, $ar);
        if (!$ar->save()) {
            throw new RuntimeException('Failed to save task: ' . implode(', ', $ar->getFirstErrors()));
        }
        if (!$task->getId() || $task->getId()->getValue() !== (int)$ar->id) {
            $task->setId(new TaskId((int)$ar->id));
        }
        return $task;
    }

    /**
     * @throws \Exception
     */
    public function findById(TaskId $id): ?Task
    {
        $ar = $this->findARById($id);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    /**
     * @throws \Exception
     */
    public function findAll(array $criteria = []): array
    {
        $query = TaskAR::find();

        if (isset($criteria['status_id'])) {
            $query->andWhere(['status_id' => $criteria['status_id']]);
        }
        if (isset($criteria['assigned_to'])) {
            $query->andWhere(['assigned_to' => $criteria['assigned_to']]);
        }
        if (isset($criteria['created_by'])) {
            $query->andWhere(['created_by' => $criteria['created_by']]);
        }
        if (isset($criteria['board_id'])) {
            $query->andWhere(['board_id' => $criteria['board_id']]);
        }
        if (isset($criteria['parent_id'])) {
            $query->andWhere(['parent_id' => $criteria['parent_id']]);
        }
        if (isset($criteria['complete'])) {
            $query->andWhere(['complete' => $criteria['complete']]);
        }
        if (isset($criteria['overdue'])) {
            $query->andWhere(['overdue' => $criteria['overdue']]);
        }
        if (empty($criteria['include_deleted'])) {
            $query->andWhere(['deleted_at' => null]);
        }

        $ars = $query->all();
        $tasks = [];
        foreach ($ars as $ar) {
            $tasks[] = $this->mapARToEntity($ar);
        }
        return $tasks;
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function remove(Task $task): void
    {
        $ar = $this->findARById($task->getId());
        $ar?->delete();
    }

    private function findARById(TaskId $id): ?TaskAR
    {
        return TaskAR::findOne($id->getValue());
    }

    public function countByColumn(ColumnId $columnId): int
    {
        return TaskAR::find()
            ->where(['column_id' => $columnId->getValue(), 'deleted_at' => null])
            ->count();
    }

    private function mapEntityToAR(Task $task, TaskAR $ar): void
    {
        $ar->title          = $task->getTitle()->getValue();
        $ar->description    = $task->getDescription();
        $ar->column_id      = $task->getColumnId()->getValue();
        $ar->priority_id    = $task->getPriorityId()->getValue();
        $ar->due_date       = $task->getDueDate()?->format('Y-m-d H:i:s');
        $ar->planned_start  = $task->getPlannedStart()?->format('Y-m-d H:i:s');
        $ar->planned_end    = $task->getPlannedEnd()?->format('Y-m-d H:i:s');
        $ar->created_by     = $task->getCreatedBy()->getValue();
        $ar->assigned_to    = $task->getAssignedTo()?->getValue();
        $ar->board_id       = $task->getBoardId();
        $ar->parent_id      = $task->getParentId()?->getValue();
        $ar->overdue        = $task->isOverdue() ? 1 : 0;
        $ar->complete       = $task->isComplete() ? 1 : 0;
        $ar->created_at     = $task->getCreatedAt()->format('Y-m-d H:i:s');
        $ar->updated_at     = $task->getUpdatedAt()->format('Y-m-d H:i:s');
        $ar->deleted_at     = $task->getDeletedAt()?->format('Y-m-d H:i:s');
    }

    /**
     * @throws \Exception
     */
    private function mapARToEntity(TaskAR $ar): Task
    {
        return new Task(
            new TaskId((int)$ar->id),
            new Title($ar->title),
            new ColumnId((int)$ar->column_id),
            new PriorityId((int)$ar->priority_id),
            new UserId((int)$ar->created_by),
            (int)$ar->board_id,
            $ar->description,
            $ar->due_date ? new DateTimeImmutable($ar->due_date) : null,
            $ar->planned_start ? new DateTimeImmutable($ar->planned_start) : null,
            $ar->planned_end ? new DateTimeImmutable($ar->planned_end) : null,
            $ar->assigned_to ? new UserId((int)$ar->assigned_to) : null,
            $ar->parent_id ? new TaskId((int)$ar->parent_id) : null,
            (bool)$ar->overdue,
            (bool)$ar->complete,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at),
            $ar->deleted_at ? new DateTimeImmutable($ar->deleted_at) : null
        );
    }
}