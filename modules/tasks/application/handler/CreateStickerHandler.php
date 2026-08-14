<?php

namespace modules\tasks\application\handler;

use DateTimeImmutable;
use InvalidArgumentException;
use modules\projects\application\port\IProjectAccess;
use modules\tasks\application\assembler\StickerDtoAssembler;
use modules\tasks\application\command\CreateStickerCommand;
use modules\tasks\application\dto\StickerDto;
use modules\tasks\domain\entity\Sticker;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\repository\IStickerRepository;
use modules\tasks\domain\valueObject\StickerId;
use modules\tasks\domain\valueObject\StickerName;
use modules\tasks\domain\valueObject\StickerType;
use modules\tasks\domain\valueObject\UserId;
use RuntimeException;

class CreateStickerHandler
{
    private IStickerRepository $stickerRepository;
    private StickerDtoAssembler $stickerDtoAssembler;
    private IEventDispatcher $eventDispatcher;
    private IProjectAccess $projectAccess;

    public function __construct(
        IStickerRepository $stickerRepository,
        StickerDtoAssembler $stickerDtoAssembler,
        IEventDispatcher $eventDispatcher,
        IProjectAccess $projectAccess
    ) {
        $this->stickerRepository    = $stickerRepository;
        $this->stickerDtoAssembler  = $stickerDtoAssembler;
        $this->eventDispatcher      = $eventDispatcher;
        $this->projectAccess        = $projectAccess;
    }

    public function handle(CreateStickerCommand $command): StickerDto
    {
        $type = new StickerType($command->type);
        if ($type->isUser() && $command->projectId === null) {
            throw new InvalidArgumentException('Project ID is required for user sticker');
        }
        if ($type->isSystem() && $command->projectId !== null) {
            throw new InvalidArgumentException('System sticker cannot have project ID');
        }
        if ($command->projectId !== null) {
            if (!$this->projectAccess->canViewProject($command->createdBy, $command->projectId)) {
                throw new RuntimeException('You are not allowed to create stickers in this project');
            }
        }

        $sticker = new Sticker(
            new StickerId(0),
            new StickerName($command->name),
            $type,
            new UserId($command->createdBy),
            $command->projectId,
            $command->data,
            $command->color,
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );

        $savedSticker = $this->stickerRepository->save($sticker);

        // $this->eventDispatcher->dispatch(new StickerCreatedEvent($savedSticker));

        return $this->stickerDtoAssembler->toDto($savedSticker);
    }
}