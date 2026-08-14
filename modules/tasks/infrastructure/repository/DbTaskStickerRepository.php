<?php

namespace modules\tasks\infrastructure\repository;

use modules\tasks\domain\repository\ITaskStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\infrastructure\persistence\TaskStickerMapAR;
use RuntimeException;
use yii\db\Connection;
use yii\db\Exception;

class DbTaskStickerRepository implements ITaskStickerRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws Exception
     */
    public function attach(TaskId $taskId, StickerId $stickerId): void
    {
        $exists = TaskStickerMapAR::find()
            ->where(['task_id' => $taskId->getValue(), 'sticker_id' => $stickerId->getValue()])
            ->exists();
        if ($exists) {
            return; // уже прикреплено
        }

        $ar = new TaskStickerMapAR();
        $ar->task_id = $taskId->getValue();
        $ar->sticker_id = $stickerId->getValue();
        if (!$ar->save()) {
            throw new RuntimeException('Failed to attach sticker to task');
        }
    }

    public function detach(TaskId $taskId, StickerId $stickerId): void
    {
        TaskStickerMapAR::deleteAll([
            'task_id' => $taskId->getValue(),
            'sticker_id' => $stickerId->getValue(),
        ]);
    }

    public function findByTask(TaskId $taskId): array
    {
        $rows = TaskStickerMapAR::find()
            ->select('sticker_id')
            ->where(['task_id' => $taskId->getValue()])
            ->asArray()
            ->all();
        return array_map(fn($row) => new StickerId((int)$row['sticker_id']), $rows);
    }

    public function isAttached(TaskId $taskId, StickerId $stickerId): bool
    {
        return TaskStickerMapAR::find()
            ->where(['task_id' => $taskId->getValue(), 'sticker_id' => $stickerId->getValue()])
            ->exists();
    }
}