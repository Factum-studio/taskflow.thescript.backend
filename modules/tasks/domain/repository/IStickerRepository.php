<?php

namespace modules\tasks\domain\repository;

use modules\tasks\domain\entity\Sticker;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\StickerType;

interface IStickerRepository
{
    public function save(Sticker $sticker): Sticker;
    public function findById(StickerId $id): ?Sticker;
    /**
     * @return Sticker[]
     */
    public function findByProject(int $projectId, ?StickerType $type = null): array;
    /**
     * @return Sticker[]
     */
    public function findSystemStickers(): array;
    public function remove(Sticker $sticker): void;
}