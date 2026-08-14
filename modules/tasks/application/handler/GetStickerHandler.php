<?php

namespace modules\tasks\application\handler;

use modules\projects\application\port\IProjectAccess;
use modules\tasks\application\assembler\StickerDtoAssembler;
use modules\tasks\application\dto\StickerDto;
use modules\tasks\application\query\GetStickerQuery;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use RuntimeException;

class GetStickerHandler
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

    public function handle(GetStickerQuery $query): StickerDto
    {
        $stickerId = new StickerId($query->id);
        $sticker = $this->stickerRepository->findById($stickerId);
        if (!$sticker) {
            throw new RuntimeException("Sticker with ID {$query->id} not found");
        }
        if ($sticker->getProjectId() !== null) {
            if (!$this->projectAccess->canViewProject($query->userId, $sticker->getProjectId())) {
                throw new RuntimeException('You are not allowed to view this sticker');
            }
        }
        return $this->stickerDtoAssembler->toDto($sticker);
    }
}