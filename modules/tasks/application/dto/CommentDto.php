<?php

namespace modules\tasks\application\dto;

class CommentDto
{
    public int $id;
    public int $taskId;
    public int $userId;
    public string $content;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data)
    {
        $this->id           = $data['id'];
        $this->taskId       = $data['taskId'];
        $this->userId       = $data['userId'];
        $this->content      = $data['content'];
        $this->createdAt    = $data['createdAt'];
        $this->updatedAt    = $data['updatedAt'];
    }
}