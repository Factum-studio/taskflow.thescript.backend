<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\StickerDtoAssembler;
use modules\tasks\application\dto\StickerDto;
use modules\tasks\application\query\ListStickersQuery;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\valueObject\StickerType;

class ListStickersHandler
{
    private IStickerRepository $stickerRepository;
    private StickerDtoAssembler $stickerDtoAssembler;

    public function __construct(
        IStickerRepository $stickerRepository,
        StickerDtoAssembler $stickerDtoAssembler
    ) {
        $this->stickerRepository    = $stickerRepository;
        $this->stickerDtoAssembler  = $stickerDtoAssembler;
    }

    /**
     * @return StickerDto[]
     */
    public function handle(ListStickersQuery $query): array
    {
        $type = $query->type ? new StickerType($query->type) : null;
        if ($type && $type->isSystem()) {
            $stickers = $this->stickerRepository->findSystemStickers();
        } elseif ($type && $type->isUser() && $query->projectId !== null) {
            $stickers = $this->stickerRepository->findByProject($query->projectId, $type);
        } else {
            // Если нет фильтров, возвращаем только системные
            $stickers = $this->stickerRepository->findSystemStickers();
        }
        // TODO: можно расширить репозиторий
        if ($query->createdBy !== null) {
            $stickers = array_filter($stickers, fn($s) => $s->getCreatedBy()->getValue() === $query->createdBy);
        }
        return $this->stickerDtoAssembler->toDtoList(array_values($stickers));
    }
}