<?php

namespace modules\tasks\application\handler;

use modules\projects\application\port\IProjectAccess;
use modules\tasks\application\assembler\StickerDtoAssembler;
use modules\tasks\application\dto\StickerDto;
use modules\tasks\application\query\ListStickersQuery;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\valueObject\StickerType;

class ListStickersHandler
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

    /**
     * @return StickerDto[]
     */
    public function handle(ListStickersQuery $query): array
    {
        $type = $query->type ? new StickerType($query->type) : null;
        if ($query->projectId !== null) {
            if (!$this->projectAccess->canViewProject($query->userId, $query->projectId)) {
                return [];
            }
        }
        // Если указан type=user и projectId, то берём стикеры проекта
        if ($type && $type->isUser() && $query->projectId !== null) {
            $stickers = $this->stickerRepository->findByProject($query->projectId, $type);
        } elseif ($type && $type->isSystem()) {
            $stickers = $this->stickerRepository->findSystemStickers();
        } else {
            // По умолчанию возвращаем системные стикеры
            $stickers = $this->stickerRepository->findSystemStickers();
        }
        if ($query->createdBy !== null) {
            $stickers = array_filter($stickers, fn($s) => $s->getCreatedBy()->getValue() === $query->createdBy);
        }
        return $this->stickerDtoAssembler->toDtoList(array_values($stickers));
    }
}