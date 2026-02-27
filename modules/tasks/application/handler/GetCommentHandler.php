<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\CommentDtoAssembler;
use modules\tasks\application\dto\CommentDto;
use modules\tasks\application\query\GetCommentQuery;
use modules\tasks\domain\repository\ICommentRepository;
use modules\tasks\domain\valueObject\CommentId;
use RuntimeException;

class GetCommentHandler
{
    private ICommentRepository $commentRepository;
    private CommentDtoAssembler $commentDtoAssembler;

    public function __construct(
        ICommentRepository $commentRepository,
        CommentDtoAssembler $commentDtoAssembler
    ) {
        $this->commentRepository    = $commentRepository;
        $this->commentDtoAssembler  = $commentDtoAssembler;
    }

    public function handle(GetCommentQuery $query): CommentDto
    {
        $commentId = new CommentId($query->commentId);
        $comment = $this->commentRepository->findById($commentId);
        if (!$comment) {
            throw new RuntimeException("Comment with ID {$query->commentId} not found.");
        }
        return $this->commentDtoAssembler->toDto($comment);
    }
}