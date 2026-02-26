<?php

namespace modules\tasks\application\handler;

use InvalidArgumentException;
use modules\tasks\application\assembler\CommentDtoAssembler;
use modules\tasks\application\command\AddCommentCommand;
use modules\tasks\application\dto\CommentDto;
use modules\tasks\domain\entity\Comment;
use modules\tasks\domain\event\CommentAddedEvent;
use modules\tasks\domain\event\IEventDispatcher;
use modules\tasks\domain\repository\ICommentRepository;
use modules\tasks\domain\repository\ITaskRepository;
use modules\tasks\domain\valueObject\CommentId;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;

class AddCommentHandler
{
    private ICommentRepository $commentRepository;
    private ITaskRepository $taskRepository;
    private CommentDtoAssembler $commentDtoAssembler;
    private IEventDispatcher $eventDispatcher;

    public function __construct(
        ICommentRepository $commentRepository,
        ITaskRepository $taskRepository,
        CommentDtoAssembler $commentDtoAssembler,
        IEventDispatcher $eventDispatcher
    ) {
        $this->commentRepository    = $commentRepository;
        $this->taskRepository       = $taskRepository;
        $this->commentDtoAssembler  = $commentDtoAssembler;
        $this->eventDispatcher      = $eventDispatcher;
    }

    public function handle(AddCommentCommand $command): CommentDto
    {
        $taskId = new TaskId($command->taskId);
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new InvalidArgumentException("Task with ID {$command->taskId} does not exist.");
        }
        // Я вам запрещаю оставлять комментарии удалённым задачам
        if ($task->getDeletedAt() !== null) {
            throw new InvalidArgumentException("Cannot comment on deleted task.");
        }

        $comment = new Comment(
            new CommentId(0),
            $taskId,
            new UserId($command->userId),
            $command->content
        );

        $savedComment = $this->commentRepository->save($comment);

        $this->eventDispatcher->dispatch(new CommentAddedEvent($savedComment));

        return $this->commentDtoAssembler->toDto($savedComment);
    }
}