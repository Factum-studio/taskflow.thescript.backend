<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\CommentDtoAssembler;
use modules\tasks\application\dto\CommentDto;
use modules\tasks\application\port\ITaskAccess;
use modules\tasks\application\query\ListCommentsQuery;
use modules\tasks\domain\repository\ICommentRepository;
use modules\tasks\domain\valueObject\TaskId;
use RuntimeException;

class ListCommentsHandler
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

    /**
     * @return CommentDto[]
     */
    public function handle(ListCommentsQuery $query): array
    {
        if (!$this->taskAccess->canViewTask($query->userId, $query->taskId)) {
            throw new RuntimeException('You are not allowed to view comments of this task');
        }

        $taskId = new TaskId($query->taskId);
        $comments = $this->commentRepository->findByTaskId($taskId);
        return $this->commentDtoAssembler->toDtoList($comments);
    }
}