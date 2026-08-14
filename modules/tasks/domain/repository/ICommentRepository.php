<?php

namespace modules\tasks\domain\repository;

use modules\tasks\domain\entity\Comment;
use modules\tasks\domain\valueObject\CommentId;
use modules\tasks\domain\valueObject\TaskId;

interface ICommentRepository
{
    public function save(Comment $comment): Comment;
    public function findById(CommentId $id): ?Comment;
    /**
     * @return Comment[]
     */
    public function findByTaskId(TaskId $taskId): array;
    public function remove(Comment $comment): void;
}