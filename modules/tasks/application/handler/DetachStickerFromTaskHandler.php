<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\command\DetachStickerFromTaskCommand;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITaskStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class DetachStickerFromTaskHandler
{
    private ITaskStickerRepository $taskStickerRepository;
    private ITaskRepository $taskRepository;
    private ITaskAccess $taskAccess;

    public function __construct(
        ITaskStickerRepository $taskStickerRepository,
        ITaskRepository $taskRepository,
        ITaskAccess $taskAccess
    ) {
        $this->taskStickerRepository    = $taskStickerRepository;
        $this->taskRepository           = $taskRepository;
        $this->taskAccess               = $taskAccess;
    }

    public function handle(DetachStickerFromTaskCommand $command): void
    {
        $taskId = new TaskId($command->taskId);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with ID {$command->taskId} not found");
        }

        if (!$this->taskAccess->canAddStickerToTask($command->detachedBy, $command->taskId)) {
            throw new RuntimeException('You are not allowed to detach stickers from this task');
        }

        $stickerId = new StickerId($command->stickerId);
        if (!$this->taskStickerRepository->isAttached($taskId, $stickerId)) {
            throw new RuntimeException('Sticker not attached to this task');
        }

        $this->taskStickerRepository->detach($taskId, $stickerId);
    }
}