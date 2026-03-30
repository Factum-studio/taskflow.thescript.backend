<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use Exception;
use modules\tasks\application\command\AssignTaskCommand;
use modules\tasks\application\command\CreateTaskCommand;
use modules\tasks\application\command\HardDeleteTaskCommand;
use modules\tasks\application\command\RestoreTaskCommand;
use modules\tasks\application\command\SoftDeleteTaskCommand;
use modules\tasks\application\command\UpdateTaskCommand;
use modules\tasks\application\handler\AssignTaskHandler;
use modules\tasks\application\handler\CreateTaskHandler;
use modules\tasks\application\handler\GetTaskHandler;
use modules\tasks\application\handler\HardDeleteTaskHandler;
use modules\tasks\application\handler\ListTasksHandler;
use modules\tasks\application\handler\RestoreTaskHandler;
use modules\tasks\application\handler\SoftDeleteTaskHandler;
use modules\tasks\application\handler\UpdateTaskHandler;
use modules\tasks\application\query\GetTaskQuery;
use modules\tasks\application\query\ListTasksQuery;
use modules\tasks\presentation\request\AssignTaskRequest;
use modules\tasks\presentation\request\CreateTaskRequest;
use modules\tasks\presentation\request\HardDeleteTaskRequest;
use modules\tasks\presentation\request\RestoreTaskRequest;
use modules\tasks\presentation\request\SoftDeleteTaskRequest;
use modules\tasks\presentation\request\UpdateTaskRequest;
use modules\tasks\application\command\MoveTaskToColumnCommand;
use modules\tasks\application\handler\MoveTaskToColumnHandler;
use modules\tasks\presentation\request\MoveTaskToColumnRequest;
use core\presentation\controller\BaseController;
use DateTimeImmutable;
use DateTimeZone;
use OpenApi\Attributes as OA;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

#[OA\Tag(
    name: 'tasks',
    description: 'Управление задачами'
)]
class TaskController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly CreateTaskHandler $createTaskHandler,
        private readonly GetTaskHandler $getTaskHandler,
        private readonly ListTasksHandler $listTasksHandler,
        private readonly UpdateTaskHandler $updateTaskHandler,
        private readonly MoveTaskToColumnHandler $moveTaskToColumnHandler,
        private readonly AssignTaskHandler $assignTaskHandler,
        private readonly SoftDeleteTaskHandler $softDeleteTaskHandler,
        private readonly RestoreTaskHandler $restoreTaskHandler,
        private readonly HardDeleteTaskHandler $hardDeleteTaskHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/task',
        description: 'Возвращает список задач с возможностью фильтрации',
        summary: 'Список задач',
        security: [['bearerAuth' => []]],
        tags: ['tasks'],
        parameters: [
            new OA\Parameter(
                name: 'columnId',
                description: 'Фильтр по колонке(статусу)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'assignedTo',
                description: 'Фильтр по исполнителю',
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
            new OA\Parameter(
                name: 'boardId',
                description: 'Фильтр по доске',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'parentId',
                description: 'Фильтр по родительской задаче',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'onlyOverdue',
                description: 'Только просроченные задачи',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'includeDeleted',
                description: 'Включать удалённые задачи',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean')
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
                            items: new OA\Items(ref: '#/components/schemas/Task')
                        ),
                        new OA\Property(
                            property: '_meta',
                            ref: '#/components/schemas/Collection/properties/_meta'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Требуется авторизация',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function actionIndex(): ErrorDto|CollectionDto
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $request = Yii::$app->request;

        $filters = [];

        $columnId = $request->get('columnId');
        if ($columnId !== null && ctype_digit($columnId)) {
            $filters['columnId'] = (int)$columnId;
        }

        $assignedTo = $request->get('assignedTo');
        if ($assignedTo !== null && ctype_digit($assignedTo)) {
            $filters['assignedTo'] = (int)$assignedTo;
        }

        $createdBy = $request->get('createdBy');
        if ($createdBy !== null && ctype_digit($createdBy)) {
            $filters['createdBy'] = (int)$createdBy;
        }

        $boardId = $request->get('boardId');
        if ($boardId !== null && ctype_digit($boardId)) {
            $filters['boardId'] = (int)$boardId;
        }

        $parentId = $request->get('parentId');
        if ($parentId !== null && ctype_digit($parentId)) {
            $filters['parentId'] = (int)$parentId;
        }

        $onlyOverdue = $request->get('onlyOverdue');
        if ($onlyOverdue !== null) {
            $filters['onlyOverdue'] = $onlyOverdue === '1' || $onlyOverdue === 'true';
        }

        $includeDeleted = $request->get('includeDeleted');
        if ($includeDeleted !== null) {
            $filters['includeDeleted'] = $includeDeleted === '1' || $includeDeleted === 'true';
        }
        $query = new ListTasksQuery($userId, $filters);
        $tasks = $this->listTasksHandler->handle($query);

        return $this->collection($tasks);
    }

    #[OA\Get(
        path: '/task/{id}',
        summary: 'Получить задачу по ID',
        security: [['bearerAuth' => []]],
        tags: ['tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
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
                            property: 'item',
                            ref: '#/components/schemas/Task'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Требуется авторизация',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Задача не найдена',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): ErrorDto|ItemDto
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }
        $query = new GetTaskQuery($id, $userId);
        try {
            $task = $this->getTaskHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->item($task);
    }

    #[OA\Post(
        path: '/task',
        summary: 'Создать новую задачу',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateTaskRequest')
        ),
        tags: ['tasks'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Задача создана',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Task'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Требуется авторизация',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionCreate(): ItemDto|ErrorDto|array
    {
        $request = new CreateTaskRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $dueDate = $request->dueDate ? new DateTimeImmutable($request->dueDate, new DateTimeZone('UTC')) : null;
        $plannedStart = $request->plannedStart ? new DateTimeImmutable($request->plannedStart, new DateTimeZone('UTC')) : null;
        $plannedEnd = $request->plannedEnd ? new DateTimeImmutable($request->plannedEnd, new DateTimeZone('UTC')) : null;

        $command = new CreateTaskCommand(
            title: $request->title,
            columnId: $request->columnId,
            priorityId: $request->priorityId,
            createdBy: $userId,
            boardId: $request->boardId,
            description: $request->description,
            dueDate: $dueDate,
            plannedStart: $plannedStart,
            plannedEnd: $plannedEnd,
            assignedTo: $request->assignedTo,
            parentId: $request->parentId
        );

        try {
            $taskDto = $this->createTaskHandler->handle($command);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to create task', 0, $e);
        }

        return $this->item($taskDto);
    }

    #[OA\Put(
        path: '/task/{id}',
        summary: 'Обновить задачу',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateTaskRequest')
        ),
        tags: ['tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Задача обновлена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Task'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Требуется авторизация',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Задача не найдена',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionUpdate(int $id): ItemDto|ErrorDto|array
    {
        $updateRequest = new UpdateTaskRequest();
        $updateRequest->load(Yii::$app->request->post(), '');
        if (!$updateRequest->validate()) {
            return $this->error('Validation failed', 422, $updateRequest->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $dueDate = $updateRequest->dueDate ? new DateTimeImmutable($updateRequest->dueDate, new DateTimeZone('UTC')) : null;
        $plannedStart = $updateRequest->plannedStart ? new DateTimeImmutable($updateRequest->plannedStart, new DateTimeZone('UTC')) : null;
        $plannedEnd = $updateRequest->plannedEnd ? new DateTimeImmutable($updateRequest->plannedEnd, new DateTimeZone('UTC')) : null;

        $command = new UpdateTaskCommand(
            id: $id,
            updatedBy: $userId,
            title: $updateRequest->title,
            description: $updateRequest->description,
            columnId: $updateRequest->columnId,
            priorityId: $updateRequest->priorityId,
            dueDate: $dueDate,
            plannedStart: $plannedStart,
            plannedEnd: $plannedEnd,
            assignedTo: $updateRequest->assignedTo,
            boardId: $updateRequest->boardId,
            parentId: $updateRequest->parentId
        );

        try {
            $taskDto = $this->updateTaskHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to update task', 0, $e);
        }

        return $this->item($taskDto);
    }

    #[OA\Post(
        path: '/task/{id}/move-to-column',
        summary: 'Переместить задачу в другую колонку',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/MoveTaskToColumnRequest')
        ),
        tags: ['tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Статус изменён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Task'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Требуется авторизация',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Задача не найдена',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionMoveToColumn(int $id): ItemDto|ErrorDto|array
    {
        $request = new MoveTaskToColumnRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new MoveTaskToColumnCommand(
            id: $id,
            columnId: $request->columnId,
            movedBy: $userId
        );

        try {
            $taskDto = $this->moveTaskToColumnHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to move task', 0, $e);
        }

        return $this->item($taskDto);
    }

    #[OA\Post(
        path: '/task/{id}/assign',
        summary: 'Назначить исполнителя задачи',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AssignTaskRequest')
        ),
        tags: ['tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Исполнитель назначен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Task'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Требуется авторизация',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Задача не найдена',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionAssign(int $id): ItemDto|ErrorDto|array
    {
        $request = new AssignTaskRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new AssignTaskCommand(
            id: $id,
            assignedTo: $request->assignedTo,
            assignedBy: $userId
        );

        try {
            $taskDto = $this->assignTaskHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to assign task', 0, $e);
        }

        return $this->item($taskDto);
    }

    #[OA\Delete(
        path: '/task/{id}',
        summary: 'Мягкое удаление задачи',
        security: [['bearerAuth' => []]],
        tags: ['tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Задача удалена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Task deleted')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Требуется авторизация',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Задача не найдена',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionDelete(int $id): SuccessDto|ErrorDto|array
    {
        $request = new SoftDeleteTaskRequest();
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new SoftDeleteTaskCommand(
            id: $id,
            deletedBy: $userId
        );

        try {
            $this->softDeleteTaskHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to delete task', 0, $e);
        }

        return $this->success(null, 'Task deleted');
    }

    #[OA\Post(
        path: '/task/{id}/restore',
        summary: 'Восстановить мягко удалённую задачу',
        security: [['bearerAuth' => []]],
        tags: ['tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Задача восстановлена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Task'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Требуется авторизация',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Задача не найдена',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionRestore(int $id): ItemDto|ErrorDto|array
    {
        $request = new RestoreTaskRequest();
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new RestoreTaskCommand(
            id: $id,
            restoredBy: $userId
        );

        try {
            $taskDto = $this->restoreTaskHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to restore task', 0, $e);
        }

        return $this->item($taskDto);
    }

    #[OA\Delete(
        path: '/task/{id}/hard',
        summary: 'Физическое удаление задачи',
        security: [['bearerAuth' => []]],
        tags: ['tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Задача удалена навсегда',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Task permanently deleted')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Требуется авторизация',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Задача не найдена',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionHardDelete(int $id): ErrorDto|SuccessDto|array
    {
        $request = new HardDeleteTaskRequest();
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new HardDeleteTaskCommand(
            id: $id
        );

        try {
            $this->hardDeleteTaskHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to hard delete task', 0, $e);
        }

        return $this->success(null, 'Task permanently deleted');
    }
}