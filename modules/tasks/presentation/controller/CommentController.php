<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use InvalidArgumentException;
use modules\tasks\application\command\AddCommentCommand;
use modules\tasks\application\command\DeleteCommentCommand;
use modules\tasks\application\command\UpdateCommentCommand;
use modules\tasks\application\handler\AddCommentHandler;
use modules\tasks\application\handler\DeleteCommentHandler;
use modules\tasks\application\handler\GetCommentHandler;
use modules\tasks\application\handler\ListCommentsHandler;
use modules\tasks\application\handler\UpdateCommentHandler;
use modules\tasks\application\query\GetCommentQuery;
use modules\tasks\application\query\ListCommentsQuery;
use modules\tasks\presentation\request\AddCommentRequest;
use modules\tasks\presentation\request\UpdateCommentRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class CommentController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly ListCommentsHandler $listCommentsHandler,
        private readonly GetCommentHandler $getCommentHandler,
        private readonly AddCommentHandler $addCommentHandler,
        private readonly UpdateCommentHandler $updateCommentHandler,
        private readonly DeleteCommentHandler $deleteCommentHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionIndex(int $taskId): CollectionDto|array
    {
        $query = new ListCommentsQuery($taskId);
        try {
            $comments = $this->listCommentsHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->collection($comments);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): ItemDto|array
    {
        $query = new GetCommentQuery($id);
        try {
            $comment = $this->getCommentHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->item($comment);
    }

    /**
     * @throws ServerErrorHttpException
     * @throws NotFoundHttpException
     */
    public function actionCreate(int $taskId): ItemDto|ErrorDto|array
    {
        $request = new AddCommentRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new AddCommentCommand($taskId, $userId, $request->content);

        try {
            $commentDto = $this->addCommentHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to add comment', 0, $e);
        }

        return $this->item($commentDto);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdate(int $id): ItemDto|ErrorDto|array
    {
        $request = new UpdateCommentRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new UpdateCommentCommand($id, $userId, $request->content);

        try {
            $commentDto = $this->updateCommentHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to update comment', 0, $e);
        }

        return $this->item($commentDto);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionDelete(int $id): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new DeleteCommentCommand($id, $userId);

        try {
            $this->deleteCommentHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to delete comment', 0, $e);
        }

        return $this->success(null, 'Comment deleted');
    }
}