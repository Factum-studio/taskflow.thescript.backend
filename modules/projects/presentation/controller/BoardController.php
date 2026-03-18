<?php

namespace modules\projects\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use modules\projects\application\command\CreateBoardCommand;
use modules\projects\application\command\DeleteBoardCommand;
use modules\projects\application\command\UpdateBoardCommand;
use modules\projects\application\handler\CreateBoardHandler;
use modules\projects\application\handler\DeleteBoardHandler;
use modules\projects\application\handler\GetBoardHandler;
use modules\projects\application\handler\ListProjectBoardsHandler;
use modules\projects\application\handler\UpdateBoardHandler;
use modules\projects\application\query\GetBoardQuery;
use modules\projects\application\query\ListProjectBoardsQuery;
use modules\projects\presentation\request\CreateBoardRequest;
use modules\projects\presentation\request\UpdateBoardRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class BoardController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly CreateBoardHandler $createBoardHandler,
        private readonly UpdateBoardHandler $updateBoardHandler,
        private readonly DeleteBoardHandler $deleteBoardHandler,
        private readonly GetBoardHandler $getBoardHandler,
        private readonly ListProjectBoardsHandler $listBoardsHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionIndex(int $projectId): CollectionDto|array
    {
        $query = new ListProjectBoardsQuery($projectId);
        try {
            $boards = $this->listBoardsHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->collection($boards);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): ItemDto|array
    {
        $query = new GetBoardQuery($id);
        try {
            $board = $this->getBoardHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->item($board);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionCreate(int $projectId): ErrorDto|ItemDto|array
    {
        $request = new CreateBoardRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new CreateBoardCommand(
            projectId: $projectId,
            name: $request->name,
            createdBy: $userId,
            description: $request->description,
            settings: $request->settings
        );

        try {
            $boardDto = $this->createBoardHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to create board', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to create board', 0, $e);
        }

        return $this->item($boardDto);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdate(int $id): ErrorDto|ItemDto|array
    {
        $request = new UpdateBoardRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new UpdateBoardCommand(
            id: $id,
            updatedBy: $userId,
            name: $request->name,
            description: $request->description,
            settings: $request->settings
        );

        try {
            $boardDto = $this->updateBoardHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to update board', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to update board', 0, $e);
        }

        return $this->item($boardDto);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionDelete(int $id): ErrorDto|SuccessDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new DeleteBoardCommand($id, $userId);

        try {
            $this->deleteBoardHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to delete board', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to delete board', 0, $e);
        }

        return $this->success(null, 'Board deleted');
    }
}