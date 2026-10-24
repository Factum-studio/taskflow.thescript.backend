<?php

declare(strict_types=1);

namespace modules\tasks\application\handler;

use modules\tasks\application\command\DetachStickerFromTaskCommand;
use modules\projectRbac\application\port\ITaskAccessService;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITaskStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class DetachStickerFromTaskHandler
{
    private ITaskStickerRepository $taskStickerRepository;
    private ITaskRepository $taskRepository;
    private ITaskAccessService $taskAccess;

    public function __construct(
        ITaskStickerRepository $taskStickerRepository,
        ITaskRepository $taskRepository,
        ITaskAccessService $taskAccess,
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
