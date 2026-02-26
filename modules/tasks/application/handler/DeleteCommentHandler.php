<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\tasks\application\command\DeleteCommentCommand;
use modules\tasks\domain\repository\ICommentRepository;
use modules\tasks\domain\valueObject\CommentId;
use RuntimeException;

class DeleteCommentHandler
{
    private ICommentRepository $commentRepository;

    public function __construct(
        ICommentRepository $commentRepository
    ) {
        $this->commentRepository = $commentRepository;
    }

    public function handle(DeleteCommentCommand $command): void
    {
        $commentId = new CommentId($command->commentId);
        $comment = $this->commentRepository->findById($commentId);
        if (!$comment) {
            throw new RuntimeException("Comment with ID {$command->commentId} not found.");
        }

        // Только автор может удалять коммент
        if ($comment->getUserId()->getValue() !== $command->userId) {
            throw new InvalidArgumentException("You are not allowed to delete this comment.");
        }

        $this->commentRepository->remove($comment);
    }
}