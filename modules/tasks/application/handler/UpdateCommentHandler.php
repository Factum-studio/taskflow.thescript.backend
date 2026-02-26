<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\tasks\application\assembler\CommentDtoAssembler;
use modules\tasks\application\command\UpdateCommentCommand;
use modules\tasks\application\dto\CommentDto;
use modules\tasks\domain\event\CommentUpdatedEvent;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\repository\ICommentRepository;
use modules\tasks\domain\valueObject\CommentId;
use RuntimeException;

class UpdateCommentHandler
{
    private ICommentRepository $commentRepository;
    private CommentDtoAssembler $commentDtoAssembler;
    private IEventDispatcher $eventDispatcher;

    public function __construct(
        ICommentRepository $commentRepository,
        CommentDtoAssembler $commentDtoAssembler,
        IEventDispatcher $eventDispatcher
    ) {
        $this->commentRepository    = $commentRepository;
        $this->commentDtoAssembler  = $commentDtoAssembler;
        $this->eventDispatcher      = $eventDispatcher;
    }

    public function handle(UpdateCommentCommand $command): CommentDto
    {
        $commentId = new CommentId($command->commentId);
        $comment = $this->commentRepository->findById($commentId);
        if (!$comment) {
            throw new RuntimeException("Comment with ID {$command->commentId} not found.");
        }

        // Я вам запрещаю редактировать чужие комментарии
        if ($comment->getUserId()->getValue() !== $command->userId) {
            throw new InvalidArgumentException("You are not allowed to edit this comment.");
        }

        $comment->changeContent($command->content);
        $savedComment = $this->commentRepository->save($comment);

        $this->eventDispatcher->dispatch(new CommentUpdatedEvent($savedComment));

        return $this->commentDtoAssembler->toDto($savedComment);
    }
}