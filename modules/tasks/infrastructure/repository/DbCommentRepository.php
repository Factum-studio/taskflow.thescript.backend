<?php

namespace modules\tasks\infrastructure\repository;

use DateTimeImmutable;
use Exception;
use modules\tasks\domain\entity\Comment;
use modules\tasks\domain\repository\ICommentRepository;
use modules\tasks\domain\valueObject\CommentId;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;
use modules\tasks\infrastructure\persistence\CommentAR;
use RuntimeException;
use Throwable;
use yii\db\Connection;
use yii\db\StaleObjectException;

class DbCommentRepository implements ICommentRepository
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function save(Comment $comment): Comment
    {
        $ar = $this->findARById($comment->getId()) ?? new CommentAR();
        $this->mapEntityToAR($comment, $ar);
        if (!$ar->save()) {
            throw new RuntimeException('Failed to save comment: ' . implode(', ', $ar->getFirstErrors()));
        }
        if (!$comment->getId() || $comment->getId()->getValue() !== (int)$ar->id) {
            $comment->setId(new CommentId((int)$ar->id));
        }
        return $comment;
    }

    /**
     * @throws Exception
     */
    public function findById(CommentId $id): ?Comment
    {
        $ar = $this->findARById($id);
        return $ar ? $this->mapARToEntity($ar) : null;
    }

    public function findByTaskId(TaskId $taskId): array
    {
        $ars = CommentAR::find()
            ->where(['task_id' => $taskId->getValue()])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
        return array_map([$this, 'mapARToEntity'], $ars);
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Comment $comment): void
    {
        $ar = $this->findARById($comment->getId());
        $ar?->delete();
    }

    private function findARById(CommentId $id): ?CommentAR
    {
        return CommentAR::findOne($id->getValue());
    }

    private function mapEntityToAR(Comment $comment, CommentAR $ar): void
    {
        $ar->task_id = $comment->getTaskId()->getValue();
        $ar->user_id = $comment->getUserId()->getValue();
        $ar->content = $comment->getContent();
    }

    /**
     * @throws Exception
     */
    private function mapARToEntity(CommentAR $ar): Comment
    {
        return new Comment(
            new CommentId((int)$ar->id),
            new TaskId((int)$ar->task_id),
            new UserId((int)$ar->user_id),
            $ar->content,
            new DateTimeImmutable($ar->created_at),
            new DateTimeImmutable($ar->updated_at)
        );
    }
}