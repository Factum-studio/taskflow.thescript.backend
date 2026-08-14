<?php

namespace modules\tasks\domain\repository;

use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\TaskId;

interface ITaskStickerRepository
{
    public function attach(TaskId $taskId, StickerId $stickerId): void;
    public function detach(TaskId $taskId, StickerId $stickerId): void;

    /**
     * Получить стикеры прикреплённые к задаче
     * @return StickerId[]
     */
    public function findByTask(TaskId $taskId): array;
    public function isAttached(TaskId $taskId, StickerId $stickerId): bool;
}