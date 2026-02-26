<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\CommentDtoAssembler;
use modules\tasks\application\dto\CommentDto;
use modules\tasks\application\query\ListCommentsQuery;
use modules\tasks\domain\repository\ICommentRepository;
use modules\tasks\domain\valueObject\TaskId;

class ListCommentsHandler
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

    /**
     * @return CommentDto[]
     */
    public function handle(ListCommentsQuery $query): array
    {
        $taskId = new TaskId($query->taskId);
        $comments = $this->commentRepository->findByTaskId($taskId);
        return $this->commentDtoAssembler->toDtoList($comments);
    }
}