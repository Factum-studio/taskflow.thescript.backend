<?php

namespace modules\tasks\domain\repository;

use DateTimeImmutable;
use modules\tasks\domain\entity\TimeInterval;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\TimeIntervalId;
use modules\tasks\domain\valueObject\UserId;

interface ITimeIntervalRepository
{
    public function save(TimeInterval $interval): TimeInterval;
    public function findById(TimeIntervalId $id): ?TimeInterval;
    /**
     * Найти все интервалы задачи для пользователя за период
     * @return TimeInterval[]
     */
    public function findByTaskAndUser(TaskId $taskId, UserId $userId, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array;
    /**
     * Найти все интервалы пользователя за период
     * @return TimeInterval[]
     */
    public function findByUser(UserId $userId, DateTimeImmutable $from, DateTimeImmutable $to): array;
    /**
     * Незавершённый интервал пользователя по задаче или вообще
     * @deprecated
     */
    public function findActiveInterval(UserId $userId, ?TaskId $taskId = null): ?TimeInterval;
    /**
     * @return TimeInterval[]
     */
    public function findAllActive(UserId $userId, ?TaskId $taskId = null): array;
    public function remove(TimeInterval $interval): void;
}