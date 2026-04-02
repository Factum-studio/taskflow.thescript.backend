<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\projects\application\port\IProjectAccess;
use modules\tasks\application\assembler\StickerDtoAssembler;
use modules\tasks\application\command\UpdateStickerCommand;
use modules\tasks\application\dto\StickerDto;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\StickerName;
use RuntimeException;

class UpdateStickerHandler
{
    private IStickerRepository $stickerRepository;
    private StickerDtoAssembler $stickerDtoAssembler;
    private IProjectAccess $projectAccess;

    public function __construct(
        IStickerRepository $stickerRepository,
        StickerDtoAssembler $stickerDtoAssembler,
        IProjectAccess $projectAccess
    ) {
        $this->stickerRepository    = $stickerRepository;
        $this->stickerDtoAssembler  = $stickerDtoAssembler;
        $this->projectAccess        = $projectAccess;
    }

    public function handle(UpdateStickerCommand $command): StickerDto
    {
        $stickerId = new StickerId($command->id);
        $sticker = $this->stickerRepository->findById($stickerId);
        if (!$sticker) {
            throw new RuntimeException("Sticker with ID {$command->id} not found");
        }

        if ($sticker->getCreatedBy()->getValue() !== $command->updatedBy) {
            throw new InvalidArgumentException('You are not allowed to update this sticker');
        }

        if ($sticker->getProjectId() !== null) {
            if (!$this->projectAccess->canViewProject($command->updatedBy, $sticker->getProjectId())) {
                throw new RuntimeException('You are not allowed to update this sticker');
            }
        }

        if ($command->name !== null) {
            $sticker->rename(new StickerName($command->name));
        }
        if ($command->data !== null) {
            $sticker->updateData($command->data);
        }
        if ($command->color !== null) {
            $sticker->changeColor($command->color);
        }

        $savedSticker = $this->stickerRepository->save($sticker);
        return $this->stickerDtoAssembler->toDto($savedSticker);
    }
}