<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use InvalidArgumentException;
use modules\tasks\application\command\CreateBoardColumnCommand;
use modules\tasks\application\command\DeleteBoardColumnCommand;
use modules\tasks\application\command\ReorderBoardColumnsCommand;
use modules\tasks\application\command\UpdateBoardColumnCommand;
use modules\tasks\application\handler\CreateBoardColumnHandler;
use modules\tasks\application\handler\DeleteBoardColumnHandler;
use modules\tasks\application\handler\GetBoardColumnHandler;
use modules\tasks\application\handler\GetBoardColumnsHandler;
use modules\tasks\application\handler\ReorderBoardColumnsHandler;
use modules\tasks\application\handler\UpdateBoardColumnHandler;
use modules\tasks\application\query\GetBoardColumnQuery;
use modules\tasks\application\query\GetBoardColumnsQuery;
use modules\tasks\presentation\request\CreateBoardColumnRequest;
use modules\tasks\presentation\request\ReorderBoardColumnsRequest;
use modules\tasks\presentation\request\UpdateBoardColumnRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'board-columns',
    description: 'Управление колонками досок (статусами задач)'
)]
class BoardColumnController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly CreateBoardColumnHandler $createHandler,
        private readonly UpdateBoardColumnHandler $updateHandler,
        private readonly DeleteBoardColumnHandler $deleteHandler,
        private readonly GetBoardColumnsHandler $getListHandler,
        private readonly GetBoardColumnHandler $getOneHandler,
        private readonly ReorderBoardColumnsHandler $reorderHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/board/{boardId}/column',
        description: 'Возвращает все колонки указанной доски',
        summary: 'Список колонок доски',
        security: [['bearerAuth' => []]],
        tags: ['board-columns'],
        parameters: [
            new OA\Parameter(
                name: 'boardId',
                description: 'ID доски',
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
                            items: new OA\Items(ref: '#/components/schemas/BoardColumn')
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
    public function actionIndex(int $boardId): CollectionDto|array
    {
        $query = new GetBoardColumnsQuery($boardId);
        $columns = $this->getListHandler->handle($query);
        return $this->collection($columns);
    }

    #[OA\Get(
        path: '/board/column/{id}',
        summary: 'Получить колонку по ID',
        security: [['bearerAuth' => []]],
        tags: ['board-columns'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID колонки',
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
                            ref: '#/components/schemas/BoardColumn'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Колонка не найдена')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): ItemDto|array
    {
        $query = new GetBoardColumnQuery($id);
        try {
            $column = $this->getOneHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->item($column);
    }

    #[OA\Post(
        path: '/board/{boardId}/column',
        summary: 'Создать новую колонку в доске',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateBoardColumnRequest')
        ),
        tags: ['board-columns'],
        parameters: [
            new OA\Parameter(
                name: 'boardId',
                description: 'ID доски',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Колонка создана',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/BoardColumn'
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
    public function actionCreate(int $boardId): ErrorDto|ItemDto|array
    {
        $request = new CreateBoardColumnRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new CreateBoardColumnCommand(
            boardId: $boardId,
            name: $request->name,
            label: $request->label,
            sortOrder: $request->sortOrder ?? 0,
            isActive: $request->isActive ?? true,
            isFinal: $request->isFinal ?? false,
            color: $request->color,
            workflowId: $request->workflowId,
            createdBy: $userId
        );

        try {
            $columnDto = $this->createHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to create board column', 0, $e);
        }

        return $this->item($columnDto);
    }

    #[OA\Put(
        path: '/board/column/{id}',
        summary: 'Обновить колонку',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateBoardColumnRequest')
        ),
        tags: ['board-columns'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID колонки',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Колонка обновлена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/BoardColumn'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Колонка не найдена'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdate(int $id): ErrorDto|ItemDto|array
    {
        $request = new UpdateBoardColumnRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new UpdateBoardColumnCommand(
            id: $id,
            updatedBy: $userId,
            name: $request->name,
            label: $request->label,
            sortOrder: $request->sortOrder,
            isActive: $request->isActive,
            isFinal: $request->isFinal,
            color: $request->color,
            workflowId: $request->workflowId
        );

        try {
            $columnDto = $this->updateHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to update board column', 0, $e);
        }

        return $this->item($columnDto);
    }

    #[OA\Delete(
        path: '/board/column/{id}',
        summary: 'Удалить колонку',
        security: [['bearerAuth' => []]],
        tags: ['board-columns'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID колонки',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Колонка удалена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Column deleted')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Колонка не найдена')
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

        $command = new DeleteBoardColumnCommand($id, $userId);

        try {
            $this->deleteHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to delete board column', 0, $e);
        }

        return $this->success(null, 'Column deleted');
    }

    #[OA\Post(
        path: '/board/{boardId}/column/reorder',
        summary: 'Изменить порядок колонок',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ReorderBoardColumnsRequest')
        ),
        tags: ['board-columns'],
        parameters: [
            new OA\Parameter(
                name: 'boardId',
                description: 'ID доски',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Порядок обновлён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Columns reordered')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionReorder(int $boardId): ErrorDto|SuccessDto|array
    {
        $request = new ReorderBoardColumnsRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new ReorderBoardColumnsCommand($boardId, $request->orderedIds, $userId);

        try {
            $this->reorderHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to reorder columns', 0, $e);
        }

        return $this->success(null, 'Columns reordered');
    }
}