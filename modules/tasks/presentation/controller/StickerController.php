<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use InvalidArgumentException;
use modules\tasks\application\command\CreateStickerCommand;
use modules\tasks\application\command\DeleteStickerCommand;
use modules\tasks\application\command\UpdateStickerCommand;
use modules\tasks\application\handler\CreateStickerHandler;
use modules\tasks\application\handler\DeleteStickerHandler;
use modules\tasks\application\handler\GetStickerHandler;
use modules\tasks\application\handler\ListStickersHandler;
use modules\tasks\application\handler\UpdateStickerHandler;
use modules\tasks\application\query\GetStickerQuery;
use modules\tasks\application\query\ListStickersQuery;
use modules\tasks\presentation\request\CreateStickerRequest;
use modules\tasks\presentation\request\UpdateStickerRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class StickerController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly CreateStickerHandler $createStickerHandler,
        private readonly UpdateStickerHandler $updateStickerHandler,
        private readonly DeleteStickerHandler $deleteStickerHandler,
        private readonly ListStickersHandler $listStickersHandler,
        private readonly GetStickerHandler $getStickerHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): CollectionDto|array
    {
        $request = Yii::$app->request;
        $query = new ListStickersQuery(
            type: $request->get('type'),
            projectId: $request->get('projectId') ? (int)$request->get('projectId') : null,
            createdBy: $request->get('createdBy') ? (int)$request->get('createdBy') : null
        );
        $stickers = $this->listStickersHandler->handle($query);
        return $this->collection($stickers);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): ItemDto|array
    {
        $query = new GetStickerQuery($id);
        try {
            $sticker = $this->getStickerHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->item($sticker);
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function actionCreate(): ErrorDto|ItemDto|array
    {
        $request = new CreateStickerRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new CreateStickerCommand(
            name: $request->name,
            type: $request->type,
            createdBy: $userId,
            projectId: $request->projectId,
            data: $request->data,
            color: $request->color
        );

        try {
            $stickerDto = $this->createStickerHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to create sticker', 0, $e);
        }

        return $this->item($stickerDto);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdate(int $id): ErrorDto|ItemDto|array
    {
        $request = new UpdateStickerRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new UpdateStickerCommand(
            id: $id,
            updatedBy: $userId,
            name: $request->name,
            data: $request->data,
            color: $request->color
        );

        try {
            $stickerDto = $this->updateStickerHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to update sticker', 0, $e);
        }

        return $this->item($stickerDto);
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

        $command = new DeleteStickerCommand($id, $userId);

        try {
            $this->deleteStickerHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to delete sticker', 0, $e);
        }

        return $this->success(null, 'Sticker deleted');
    }
}