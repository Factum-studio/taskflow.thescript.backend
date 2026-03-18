<?php

namespace modules\projects\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use modules\projects\application\command\CreateProjectCommand;
use modules\projects\application\command\DeleteProjectCommand;
use modules\projects\application\command\UpdateProjectCommand;
use modules\projects\application\handler\CreateProjectHandler;
use modules\projects\application\handler\DeleteProjectHandler;
use modules\projects\application\handler\GetProjectHandler;
use modules\projects\application\handler\ListUserProjectsHandler;
use modules\projects\application\handler\UpdateProjectHandler;
use modules\projects\application\query\GetProjectQuery;
use modules\projects\application\query\ListUserProjectsQuery;
use modules\projects\presentation\request\CreateProjectRequest;
use modules\projects\presentation\request\UpdateProjectRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ProjectController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly CreateProjectHandler $createProjectHandler,
        private readonly UpdateProjectHandler $updateProjectHandler,
        private readonly DeleteProjectHandler $deleteProjectHandler,
        private readonly GetProjectHandler $getProjectHandler,
        private readonly ListUserProjectsHandler $listUserProjectsHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): CollectionDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $query = new ListUserProjectsQuery($userId);
        $projects = $this->listUserProjectsHandler->handle($query);

        return $this->collection($projects);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): ItemDto|array
    {
        $query = new GetProjectQuery($id);
        try {
            $project = $this->getProjectHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->item($project);
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function actionCreate(): ItemDto|ErrorDto|array
    {
        $request = new CreateProjectRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new CreateProjectCommand(
            name: $request->name,
            type: $request->type,
            ownerId: $userId,
            settings: $request->settings
        );

        try {
            $projectDto = $this->createProjectHandler->handle($command);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to create project', 0, $e);
        }

        return $this->item($projectDto);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdate(int $id): ErrorDto|ItemDto|array
    {
        $request = new UpdateProjectRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new UpdateProjectCommand(
            id: $id,
            updatedBy: $userId,
            name: $request->name,
            settings: $request->settings
        );

        try {
            $projectDto = $this->updateProjectHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to update project', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to update project', 0, $e);
        }

        return $this->item($projectDto);
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

        $command = new DeleteProjectCommand($id, $userId);

        try {
            $this->deleteProjectHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed') || str_contains($e->getMessage(), 'cannot be deleted')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to delete project', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to delete project', 0, $e);
        }

        return $this->success(null, 'Project deleted');
    }
}