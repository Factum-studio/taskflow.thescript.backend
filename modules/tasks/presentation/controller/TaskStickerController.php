<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use InvalidArgumentException;
use modules\tasks\application\command\AttachStickerToTaskCommand;
use modules\tasks\application\command\DetachStickerFromTaskCommand;
use modules\tasks\application\handler\AttachStickerToTaskHandler;
use modules\tasks\application\handler\DetachStickerFromTaskHandler;
use modules\tasks\application\handler\GetTaskStickersHandler;
use modules\tasks\application\query\GetTaskStickersQuery;
use modules\tasks\presentation\request\AttachStickerRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'task-stickers',
    description: 'Управление стикерами на задачах'
)]
class TaskStickerController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly GetTaskStickersHandler $getTaskStickersHandler,
        private readonly AttachStickerToTaskHandler $attachStickerHandler,
        private readonly DetachStickerFromTaskHandler $detachStickerHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/task/{taskId}/sticker',
        summary: 'Получить стикеры задачи',
        security: [['bearerAuth' => []]],
        tags: ['task-stickers'],
        parameters: [
            new OA\Parameter(
                name: 'taskId',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Sticker')
                        ),
                        new OA\Property(
                            property: '_meta',
                            ref: '#/components/schemas/Collection/properties/_meta'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Задача не найдена')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     */
    public function actionIndex(int $taskId): CollectionDto|array
    {
        $query = new GetTaskStickersQuery($taskId);
        try {
            $stickers = $this->getTaskStickersHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->collection($stickers);
    }

    #[OA\Post(
        path: '/task/{taskId}/sticker',
        summary: 'Прикрепить стикер к задаче',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AttachStickerRequest')
        ),
        tags: ['task-stickers'],
        parameters: [
            new OA\Parameter(
                name: 'taskId',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Стикер прикреплён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Sticker attached')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Задача или стикер не найдены'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionAttach(int $taskId): ErrorDto|SuccessDto|array
    {
        $request = new AttachStickerRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new AttachStickerToTaskCommand($taskId, $request->stickerId, $userId);

        try {
            $this->attachStickerHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to attach sticker', 0, $e);
        }

        return $this->success(null, 'Sticker attached');
    }

    #[OA\Delete(
        path: '/task/{taskId}/sticker/{stickerId}',
        summary: 'Открепить стикер от задачи',
        security: [['bearerAuth' => []]],
        tags: ['task-stickers'],
        parameters: [
            new OA\Parameter(
                name: 'taskId',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'stickerId',
                description: 'ID стикера',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Стикер откреплён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Sticker detached')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Задача или стикер не найдены')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionDetach(int $taskId, int $stickerId): ErrorDto|SuccessDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new DetachStickerFromTaskCommand($taskId, $stickerId, $userId);

        try {
            $this->detachStickerHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to detach sticker', 0, $e);
        }

        return $this->success(null, 'Sticker detached');
    }
}