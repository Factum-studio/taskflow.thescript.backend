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
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'stickers',
    description: 'Управление стикерами (тегами)'
)]
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

    #[OA\Get(
        path: '/sticker',
        summary: 'Список стикеров',
        security: [['bearerAuth' => []]],
        tags: ['stickers'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                description: 'Тип стикеров (системные или пользовательские)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['system', 'user'])
            ),
            new OA\Parameter(
                name: 'projectId',
                description: 'ID проекта (обязателен для type=user)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'createdBy',
                description: 'Фильтр по создателю',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
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
            new OA\Response(response: 401, description: 'Требуется авторизация')
        ]
    )]
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

    #[OA\Get(
        path: '/sticker/{id}',
        summary: 'Получить стикер по ID',
        security: [['bearerAuth' => []]],
        tags: ['stickers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID стикера',
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
                            property: 'item',
                            ref: '#/components/schemas/Sticker'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Стикер не найден')
        ]
    )]
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

    #[OA\Post(
        path: '/sticker',
        summary: 'Создать новый стикер',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateStickerRequest')
        ),
        tags: ['stickers'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Стикер создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Sticker'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
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

    #[OA\Put(
        path: '/sticker/{id}',
        summary: 'Обновить стикер',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateStickerRequest')
        ),
        tags: ['stickers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID стикера',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Стикер обновлён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Sticker'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён (не автор)'),
            new OA\Response(response: 404, description: 'Стикер не найден'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
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

    #[OA\Delete(
        path: '/sticker/{id}',
        summary: 'Удалить стикер',
        security: [['bearerAuth' => []]],
        tags: ['stickers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID стикера',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Стикер удалён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Sticker deleted')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён (не автор)'),
            new OA\Response(response: 404, description: 'Стикер не найден')
        ]
    )]
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