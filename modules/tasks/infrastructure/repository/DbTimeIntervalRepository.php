<?php

namespace modules\tasks\infrastructure\repository;

use DateTimeImmutable;
use modules\tasks\domain\entity\TimeInterval;
use modules\tasks\domain\repository\ITimeIntervalRepository;
use modules\tasks\domain\valueObject\Duration;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\TimeIntervalId;
use modules\tasks\domain\valueObject\UserId;
use modules\tasks\infrastructure\persistence\TimeIntervalAR;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use yii\db\Exception;
use Exception as Ex;
use yii\db\StaleObjectException;

class DbTimeIntervalRepository implements ITimeIntervalRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws Exception
     */
    public function save(TimeInterval $interval): TimeInterval
    {
        $ar = $this->findARById($interval->getId()) ?? new TimeIntervalAR();
        $this->mapEntityToAR($interval, $ar);
        if (!$ar->save()) {
            throw new RuntimeException('Failed to save time interval: ' . implode(', ', $ar->getFirstErrors()));
        }
        if (!$interval->getId() || $interval->getId()->getValue() !== (int)$ar->id) {
            $interval->setId(new TimeIntervalId((int)$ar->id));
        }
        return $interval;
    }

    public function findById(TimeIntervalId $id): ?TimeInterval
    {
        $ar = $this->findARById($id);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findByTaskAndUser(TaskId $taskId, UserId $userId, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $query = TimeIntervalAR::find()
            ->where(['task_id' => $taskId->getValue(), 'user_id' => $userId->getValue()]);
        if ($from) {
            $query->andWhere(['>=', 'start_time', $from->format('Y-m-d H:i:s')]);
        }
        if ($to) {
            $query->andWhere(['<=', 'end_time', $to->format('Y-m-d H:i:s')]);
        }
        $ars = $query->orderBy(['start_time' => SORT_ASC])->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    public function findByUser(UserId $userId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $ars = TimeIntervalAR::find()
            ->where(['user_id' => $userId->getValue()])
            ->andWhere(['>=', 'start_time', $from->format('Y-m-d H:i:s')])
            ->andWhere(['<=', 'end_time', $to->format('Y-m-d H:i:s')])
            ->orderBy(['start_time' => SORT_ASC])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    public function findActiveInterval(UserId $userId, ?TaskId $taskId = null): ?TimeInterval
    {
        $query = TimeIntervalAR::find()
            ->where(['user_id' => $userId->getValue(), 'end_time' => null]);
        if ($taskId) {
            $query->andWhere(['task_id' => $taskId->getValue()]);
        }
        $ar = $query->one();
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findAllActive(UserId $userId, ?TaskId $taskId = null): array
    {
        $query = TimeIntervalAR::find()
            ->where(['user_id' => $userId->getValue(), 'end_time' => null]);
        if ($taskId) {
            $query->andWhere(['task_id' => $taskId->getValue()]);
        }
        $ars = $query->orderBy(['start_time' => SORT_ASC])->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(TimeInterval $interval): void
    {
        $ar = $this->findARById($interval->getId());
        $ar?->delete();
    }

    private function findARById(TimeIntervalId $id): ?TimeIntervalAR
    {
        return TimeIntervalAR::findOne($id->getValue());
    }

    private function mapEntityToAR(TimeInterval $interval, TimeIntervalAR $ar): void
    {
        $ar->task_id    = $interval->getTaskId()->getValue();
        $ar->user_id    = $interval->getUserId()->getValue();
        $ar->start_time = $interval->getStartTime()->format('Y-m-d H:i:s');
        $ar->end_time   = $interval->getEndTime()?->format('Y-m-d H:i:s');
        $ar->duration   = $interval->getDurationSeconds();
        $ar->comment    = $interval->getComment();
    }

    /**
     * @throws Ex
     */
    private function mapARToEntity(TimeIntervalAR $ar): TimeInterval
    {
        return new TimeInterval(
            new TimeIntervalId((int)$ar->id),
            new TaskId((int)$ar->task_id),
            new UserId((int)$ar->user_id),
            new DateTimeImmutable($ar->start_time),
            $ar->end_time ? new DateTimeImmutable($ar->end_time) : null,
            $ar->duration !== null ? new Duration((int)$ar->duration) : null,
            $ar->comment,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}