<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\tasks\application\command\DeleteStickerCommand;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use RuntimeException;

class DeleteStickerHandler
{
    private IStickerRepository $stickerRepository;

    public function __construct(IStickerRepository $stickerRepository)
    {
        $this->stickerRepository = $stickerRepository;
    }

    public function handle(DeleteStickerCommand $command): void
    {
        $stickerId = new StickerId($command->id);
        $sticker = $this->stickerRepository->findById($stickerId);
        if (!$sticker) {
            throw new RuntimeException("Sticker with ID {$command->id} not found");
        }

        if ($sticker->getCreatedBy()->getValue() !== $command->deletedBy) {
            throw new InvalidArgumentException('You are not allowed to delete this sticker');
        }

        $this->stickerRepository->remove($sticker);
    }
}