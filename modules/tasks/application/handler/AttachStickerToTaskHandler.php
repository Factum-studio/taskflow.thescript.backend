<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\tasks\application\command\AttachStickerToTaskCommand;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\event\StickerAttachedToTaskEvent;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITaskStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class AttachStickerToTaskHandler
{
    private ITaskStickerRepository $taskStickerRepository;
    private ITaskRepository $taskRepository;
    private IStickerRepository $stickerRepository;
    private IEventDispatcher $eventDispatcher;

    public function __construct(
        ITaskStickerRepository $taskStickerRepository,
        ITaskRepository $taskRepository,
        IStickerRepository $stickerRepository,
        IEventDispatcher $eventDispatcher
    ) {
        $this->taskStickerRepository    = $taskStickerRepository;
        $this->taskRepository           = $taskRepository;
        $this->stickerRepository        = $stickerRepository;
        $this->eventDispatcher          = $eventDispatcher;
    }

    public function handle(AttachStickerToTaskCommand $command): void
    {
        $taskId = new TaskId($command->taskId);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with ID {$command->taskId} not found");
        }
        if ($task->getDeletedAt() !== null) {
            throw new InvalidArgumentException('Cannot attach sticker to deleted task');
        }

        $stickerId = new StickerId($command->stickerId);
        $sticker = $this->stickerRepository->findById($stickerId);
        if (!$sticker) {
            throw new RuntimeException("Sticker with ID {$command->stickerId} not found");
        }

        $this->taskStickerRepository->attach($taskId, $stickerId);
        $this->eventDispatcher->dispatch(new StickerAttachedToTaskEvent($taskId, $stickerId, $command->attachedBy));
    }
}