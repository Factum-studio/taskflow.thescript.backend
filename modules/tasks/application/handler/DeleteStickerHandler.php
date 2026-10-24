<?php

declare(strict_types=1);

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\projectRbac\application\port\IProjectAccessService;
use modules\tasks\application\command\DeleteStickerCommand;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use RuntimeException;

class DeleteStickerHandler
{
    private IStickerRepository $stickerRepository;
    private IProjectAccessService $projectAccess;

    public function __construct(
        IStickerRepository $stickerRepository,
        IProjectAccessService $projectAccess,
    ) {
        $this->stickerRepository    = $stickerRepository;
        $this->projectAccess        = $projectAccess;
    }

    public function handle(DeleteStickerCommand $command): void
    {
        $stickerId = new StickerId($command->id);
        $sticker = $this->stickerRepository->findById($stickerId);
        if (!$sticker) {
            throw new RuntimeException("Sticker with ID {$command->id} not found");
        }

        if ($sticker->getCreatedBy()->value() !== $command->deletedBy) {
            throw new InvalidArgumentException('You are not allowed to delete this sticker');
        }

        if ($sticker->getProjectId() !== null) {
            if (!$this->projectAccess->canViewProject($command->deletedBy, $sticker->getProjectId())) {
                throw new RuntimeException('You are not allowed to delete this sticker');
            }
        }

        $this->stickerRepository->remove($sticker);
    }
}
