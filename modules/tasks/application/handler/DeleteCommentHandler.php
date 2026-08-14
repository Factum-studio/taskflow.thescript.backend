<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\tasks\application\command\DeleteCommentCommand;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\domain\repository\ICommentRepository;
use modules\tasks\domain\valueObject\CommentId;
use RuntimeException;

class DeleteCommentHandler
{
    private ICommentRepository $commentRepository;
    private ITaskAccess $taskAccess;

    public function __construct(
        ICommentRepository $commentRepository,
        ITaskAccess $taskAccess
    ) {
        $this->commentRepository    = $commentRepository;
        $this->taskAccess           = $taskAccess;
    }

    public function handle(DeleteCommentCommand $command): void
    {
        $commentId = new CommentId($command->commentId);
        $comment = $this->commentRepository->findById($commentId);
        if (!$comment) {
            throw new RuntimeException("Comment with ID {$command->commentId} not found.");
        }

        // Только автор может удалять коммент TODO: унифицировать через интерфейс
        if ($comment->getUserId()->getValue() !== $command->userId) {
            throw new InvalidArgumentException("You are not allowed to delete this comment.");
        }
        //TODO: разделить проверки для комментариев
        if (!$this->taskAccess->canCommentOnTask($command->userId, $comment->getTaskId()->getValue())) {
            throw new RuntimeException('You are not allowed to delete comments on this task');
        }

        $this->commentRepository->remove($comment);
    }
}