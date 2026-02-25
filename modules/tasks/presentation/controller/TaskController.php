<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use Exception;
use modules\tasks\application\command\AssignTaskCommand;
use modules\tasks\application\command\ChangeTaskStatusCommand;
use modules\tasks\application\command\CreateTaskCommand;
use modules\tasks\application\command\HardDeleteTaskCommand;
use modules\tasks\application\command\RestoreTaskCommand;
use modules\tasks\application\command\SoftDeleteTaskCommand;
use modules\tasks\application\command\UpdateTaskCommand;
use modules\tasks\application\handler\AssignTaskHandler;
use modules\tasks\application\handler\ChangeTaskStatusHandler;
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
use modules\tasks\presentation\request\ChangeTaskStatusRequest;
use modules\tasks\presentation\request\CreateTaskRequest;
use modules\tasks\presentation\request\HardDeleteTaskRequest;
use modules\tasks\presentation\request\RestoreTaskRequest;
use modules\tasks\presentation\request\SoftDeleteTaskRequest;
use modules\tasks\presentation\request\UpdateTaskRequest;
use core\presentation\controller\BaseController;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class TaskController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly CreateTaskHandler $createTaskHandler,
        private readonly GetTaskHandler $getTaskHandler,
        private readonly ListTasksHandler $listTasksHandler,
        private readonly UpdateTaskHandler $updateTaskHandler,
        private readonly ChangeTaskStatusHandler $changeTaskStatusHandler,
        private readonly AssignTaskHandler $assignTaskHandler,
        private readonly SoftDeleteTaskHandler $softDeleteTaskHandler,
        private readonly RestoreTaskHandler $restoreTaskHandler,
        private readonly HardDeleteTaskHandler $hardDeleteTaskHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): CollectionDto
    {
        $request = Yii::$app->request;

        $filters = [];

        $statusId = $request->get('statusId');
        if ($statusId !== null && ctype_digit($statusId)) {
            $filters['statusId'] = (int)$statusId;
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
        $query = new ListTasksQuery($filters);
        $tasks = $this->listTasksHandler->handle($query);

        return $this->collection($tasks);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): ItemDto
    {
        $query = new GetTaskQuery($id);
        try {
            $task = $this->getTaskHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->item($task);
    }

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

        $command = new CreateTaskCommand(
            title: $request->title,
            statusId: $request->statusId,
            priorityId: $request->priorityId,
            createdBy: $userId,
            boardId: $request->boardId,
            description: $request->description,
            dueDate: $dueDate,
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

        $command = new UpdateTaskCommand(
            id: $id,
            updatedBy: $userId,
            title: $updateRequest->title,
            description: $updateRequest->description,
            statusId: $updateRequest->statusId,
            priorityId: $updateRequest->priorityId,
            dueDate: $dueDate,
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

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionChangeStatus(int $id): ItemDto|ErrorDto|array
    {
        $request = new ChangeTaskStatusRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new ChangeTaskStatusCommand(
            id: $id,
            statusId: $request->statusId,
            changedBy: $userId
        );

        try {
            $taskDto = $this->changeTaskStatusHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to change task status', 0, $e);
        }

        return $this->item($taskDto);
    }

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