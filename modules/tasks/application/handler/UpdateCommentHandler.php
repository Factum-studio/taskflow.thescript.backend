<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\tasks\application\assembler\CommentDtoAssembler;
use modules\tasks\application\command\UpdateCommentCommand;
use modules\tasks\application\dto\CommentDto;
use modules\tasks\application\port\ITaskAccess;
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
    private ITaskAccess $taskAccess;

    public function __construct(
        ICommentRepository $commentRepository,
        CommentDtoAssembler $commentDtoAssembler,
        IEventDispatcher $eventDispatcher,
        ITaskAccess $taskAccess
    ) {
        $this->commentRepository    = $commentRepository;
        $this->commentDtoAssembler  = $commentDtoAssembler;
        $this->eventDispatcher      = $eventDispatcher;
        $this->taskAccess           = $taskAccess;
    }

    public function handle(UpdateCommentCommand $command): CommentDto
    {
        $commentId = new CommentId($command->commentId);
        $comment = $this->commentRepository->findById($commentId);
        if (!$comment) {
            throw new RuntimeException("Comment with ID {$command->commentId} not found.");
        }

        // Я вам запрещаю редактировать чужие комментарии TODO: унифицировать через интерфейс
        if ($comment->getUserId()->getValue() !== $command->userId) {
            throw new InvalidArgumentException("You are not allowed to edit this comment.");
        }

        if (!$this->taskAccess->canCommentOnTask($command->userId, $comment->getTaskId()->getValue())) {
            throw new RuntimeException('You are not allowed to edit comments on this task');
        }

        $comment->changeContent($command->content);
        $savedComment = $this->commentRepository->save($comment);

        $this->eventDispatcher->dispatch(new CommentUpdatedEvent($savedComment));

        return $this->commentDtoAssembler->toDto($savedComment);
    }
}