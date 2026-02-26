<?php

namespace modules\tasks\application\assembler;

use modules\tasks\application\dto\StickerDto;
use modules\tasks\domain\entity\Sticker;

class StickerDtoAssembler
{
    public function toDto(Sticker $sticker): StickerDto
    {
        return new StickerDto([
            'id'        => $sticker->getId()->getValue(),
            'name'      => $sticker->getName()->getValue(),
            'type'      => $sticker->getType()->getValue(),
            'projectId' => $sticker->getProjectId(),
            'data'      => $sticker->getData(),
            'color'     => $sticker->getColor(),
            'createdBy' => $sticker->getCreatedBy()->getValue(),
            'createdAt' => $sticker->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $sticker->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Sticker[] $stickers
     * @return StickerDto[]
     */
    public function toDtoList(array $stickers): array
    {
        return array_map([$this, 'toDto'], $stickers);
    }
}