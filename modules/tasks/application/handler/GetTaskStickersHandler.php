<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\StickerDtoAssembler;
use modules\tasks\application\dto\StickerDto;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\application\query\GetTaskStickersQuery;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\repository\ITaskStickerRepository;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class GetTaskStickersHandler
{
    private ITaskStickerRepository $taskStickerRepository;
    private IStickerRepository $stickerRepository;
    private StickerDtoAssembler $stickerDtoAssembler;
    private ITaskRepository $taskRepository;
    private ITaskAccess $taskAccess;

    public function __construct(
        ITaskStickerRepository $taskStickerRepository,
        IStickerRepository $stickerRepository,
        StickerDtoAssembler $stickerDtoAssembler,
        ITaskRepository $taskRepository,
        ITaskAccess $taskAccess
    ) {
        $this->taskStickerRepository    = $taskStickerRepository;
        $this->stickerRepository        = $stickerRepository;
        $this->stickerDtoAssembler      = $stickerDtoAssembler;
        $this->taskRepository           = $taskRepository;
        $this->taskAccess               = $taskAccess;
    }

    /**
     * @return StickerDto[]
     */
    public function handle(GetTaskStickersQuery $query): array
    {
        $taskId = new TaskId($query->taskId);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new RuntimeException("Task with ID {$query->taskId} not found");
        }

        if (!$this->taskAccess->canViewTask($query->userId, $query->taskId)) {
            throw new RuntimeException('You are not allowed to view stickers of this task');
        }

        $stickerIds = $this->taskStickerRepository->findByTask($taskId);
        $stickers = [];
        foreach ($stickerIds as $stickerId) {
            $sticker = $this->stickerRepository->findById($stickerId);
            if ($sticker) {
                $stickers[] = $sticker;
            }
        }
        return $this->stickerDtoAssembler->toDtoList($stickers);
    }
}