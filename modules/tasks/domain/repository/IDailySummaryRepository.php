<?php

namespace modules\tasks\domain\repository;

use DateTimeImmutable;
use modules\tasks\domain\entity\DailySummary;
use modules\tasks\domain\valueObject\Date;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;

interface IDailySummaryRepository
{
    public function findOrCreate(TaskId $taskId, UserId $userId, Date $date): DailySummary;
    public function save(DailySummary $summary): void;

    /**
     * Суммарное время за день для пользователя (по всем задачам)
     */
    public function getTotalForUser(UserId $userId, Date $date): int;

    /**
     * Получить список сводок за день для пользователя (по задачам)
     * @return DailySummary[]
     */
    public function findByUserAndDate(UserId $userId, Date $date): array;

    /**
     * Получить сводку за период для пользователя
     */
    public function getTotalForPeriod(UserId $userId, Date $from, Date $to): array;
}