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
use OpenApi\Attributes as OA;
use yii\web\UnauthorizedHttpException;

#[OA\Tag(
    name: 'boards',
    description: 'Управление досками проектов(projects)'
)]
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

    #[OA\Get(
        path: '/project/{projectId}/board',
        description: 'Возвращает все доски указанного проекта',
        summary: 'Список досок проекта',
        security: [['bearerAuth' => []]],
        tags: ['boards'],
        parameters: [
            new OA\Parameter(
                name: 'projectId',
                description: 'ID проекта',
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
                            items: new OA\Items(ref: '#/components/schemas/Board')
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
            new OA\Response(response: 404, description: 'Проект не найден')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionIndex(int $projectId): CollectionDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            throw new UnauthorizedHttpException('User not authenticated');
        }
        $query = new ListProjectBoardsQuery($projectId, $userId);
        try {
            $boards = $this->listBoardsHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->collection($boards);
    }

    #[OA\Get(
        path: '/board/{id}',
        summary: 'Получить доску по ID',
        security: [['bearerAuth' => []]],
        tags: ['boards'],
        parameters: [
            new OA\Parameter(
                name: 'id',
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
                            property: 'item',
                            ref: '#/components/schemas/Board'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Доска не найдена')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionView(int $id): ItemDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            throw new UnauthorizedHttpException('User not authenticated');
        }
        $query = new GetBoardQuery($id, $userId);
        try {
            $board = $this->getBoardHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->item($board);
    }

    #[OA\Post(
        path: '/project/{projectId}/board',
        summary: 'Создать новую доску в проекте',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateBoardRequest')
        ),
        tags: ['boards'],
        parameters: [
            new OA\Parameter(
                name: 'projectId',
                description: 'ID проекта',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Доска создана',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Board'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Проект не найден'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
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

    #[OA\Put(
        path: '/board/{id}',
        summary: 'Обновить доску',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateBoardRequest')
        ),
        tags: ['boards'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID доски',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Доска обновлена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Board'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Доска не найдена'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
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

    #[OA\Delete(
        path: '/board/{id}',
        summary: 'Удалить доску',
        security: [['bearerAuth' => []]],
        tags: ['boards'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID доски',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Доска удалена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Board deleted')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Доска не найдена')
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