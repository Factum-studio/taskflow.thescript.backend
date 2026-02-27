<?php

namespace modules\tasks\application\assembler;

use modules\tasks\application\dto\CommentDto;
use modules\tasks\domain\entity\Comment;

class CommentDtoAssembler
{
    public function toDto(Comment $comment): CommentDto
    {
        return new CommentDto([
            'id'        => $comment->getId()->getValue(),
            'taskId'    => $comment->getTaskId()->getValue(),
            'userId'    => $comment->getUserId()->getValue(),
            'content'   => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $comment->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Comment[] $comments
     * @return CommentDto[]
     */
    public function toDtoList(array $comments): array
    {
        return array_map([$this, 'toDto'], $comments);
    }
}