<?php

namespace modules\tasks\infrastructure\repository;

use DateTimeImmutable;
use modules\tasks\domain\entity\DailySummary;
use modules\tasks\domain\repository\IDailySummaryRepository;
use modules\tasks\domain\valueObject\DailySummaryId;
use modules\tasks\domain\valueObject\Date;
use modules\tasks\domain\valueObject\Duration;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;
use modules\tasks\infrastructure\persistence\DailySummaryAR;
use RuntimeException;
use yii\db\Connection;
use yii\db\Exception;
use Exception as Ex;

class DbDailySummaryRepository implements IDailySummaryRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws Exception
     * @throws Ex
     */
    public function findOrCreate(TaskId $taskId, UserId $userId, Date $date): DailySummary
    {
        $ar = DailySummaryAR::findOne([
            'task_id' => $taskId->getValue(),
            'user_id' => $userId->getValue(),
            'date' => $date->toString(),
        ]);
        if ($ar) {
            return $this->mapARToEntity($ar);
        }

        $ar = new DailySummaryAR();
        $ar->task_id = $taskId->getValue();
        $ar->user_id = $userId->getValue();
        $ar->date = $date->toString();
        $ar->total_duration = 0;
        if (!$ar->save()) {
            throw new RuntimeException('Failed to create daily summary');
        }
        return new DailySummary(
            new DailySummaryId((int)$ar->id),
            $taskId,
            $userId,
            $date,
            new Duration(0),
            new DateTimeImmutable($ar->updated_at)
        );
    }

    /**
     * @throws Exception
     */
    public function save(DailySummary $summary): void
    {
        $ar = DailySummaryAR::findOne($summary->getId()->getValue());
        if (!$ar) {
            $ar = new DailySummaryAR();
            $ar->id = $summary->getId()->getValue();
        }
        $ar->total_duration = $summary->getTotalDuration()->getSeconds();
        if (!$ar->save()) {
            throw new RuntimeException('Failed to save daily summary');
        }
    }

    public function getTotalForUser(UserId $userId, Date $date): int
    {
        return (int)DailySummaryAR::find()
            ->where(['user_id' => $userId->getValue(), 'date' => $date->toString()])
            ->sum('total_duration');
    }

    public function findByUserAndDate(UserId $userId, Date $date): array
    {
        $ars = DailySummaryAR::find()
            ->where(['user_id' => $userId->getValue(), 'date' => $date->toString()])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    public function getTotalForPeriod(UserId $userId, Date $from, Date $to): array
    {
        // Возвращаем массив [date => totalSeconds] за период
        $rows = DailySummaryAR::find()
            ->select(['date', 'SUM(total_duration) as total'])
            ->where(['user_id' => $userId->getValue()])
            ->andWhere(['>=', 'date', $from->toString()])
            ->andWhere(['<=', 'date', $to->toString()])
            ->groupBy('date')
            ->asArray()
            ->all();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['date']] = (int)$row['total'];
        }
        return $result;
    }

    /**
     * @throws Ex
     */
    private function mapARToEntity(DailySummaryAR $ar): DailySummary
    {
        return new DailySummary(
            new DailySummaryId((int)$ar->id),
            new TaskId((int)$ar->task_id),
            new UserId((int)$ar->user_id),
            Date::fromString($ar->date),
            new Duration((int)$ar->total_duration),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}