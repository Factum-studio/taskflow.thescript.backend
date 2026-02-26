<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\command\DetachStickerFromTaskCommand;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITaskStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class DetachStickerFromTaskHandler
{
    private ITaskStickerRepository $taskStickerRepository;
    private ITaskRepository $taskRepository;

    public function __construct(
        ITaskStickerRepository $taskStickerRepository,
        ITaskRepository $taskRepository,
    ) {
        $this->taskStickerRepository    = $taskStickerRepository;
        $this->taskRepository           = $taskRepository;
    }

    public function handle(DetachStickerFromTaskCommand $command): void
    {
        $taskId = new TaskId($command->taskId);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with ID {$command->taskId} not found");
        }

        $stickerId = new StickerId($command->stickerId);
        if (!$this->taskStickerRepository->isAttached($taskId, $stickerId)) {
            throw new RuntimeException('Sticker not attached to this task');
        }

        $this->taskStickerRepository->detach($taskId, $stickerId);
    }
}