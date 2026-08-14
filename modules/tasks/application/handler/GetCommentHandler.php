<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\CommentDtoAssembler;
use modules\tasks\application\dto\CommentDto;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\application\query\GetCommentQuery;
use modules\tasks\domain\repository\ICommentRepository;
use modules\tasks\domain\valueObject\CommentId;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class GetCommentHandler
{
    private ICommentRepository $commentRepository;
    private CommentDtoAssembler $commentDtoAssembler;
    private ITaskAccess $taskAccess;

    public function __construct(
        ICommentRepository $commentRepository,
        CommentDtoAssembler $commentDtoAssembler,
        ITaskAccess $taskAccess
    ) {
        $this->commentRepository    = $commentRepository;
        $this->commentDtoAssembler  = $commentDtoAssembler;
        $this->taskAccess           = $taskAccess;
    }

    public function handle(GetCommentQuery $query): CommentDto
    {
        $commentId = new CommentId($query->commentId);
        $comment = $this->commentRepository->findById($commentId);
        if (!$comment) {
            throw new RuntimeException("Comment with ID {$query->commentId} not found.");
        }
        if (!$this->taskAccess->canViewTask($query->userId, $comment->getTaskId()->getValue())) {
            throw new RuntimeException('You are not allowed to view this comment');
        }
        return $this->commentDtoAssembler->toDto($comment);
    }
}