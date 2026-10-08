<?php

namespace modules\tasks\domain\repository;

use core\domain\valueObject\Date;
use core\domain\valueObject\UserId;
use modules\tasks\domain\entity\DailySummary;
use modules\tasks\domain\valueObject\TaskId;

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