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
use OpenApi\Attributes as OA;
use yii\web\UnauthorizedHttpException;

#[OA\Tag(
    name: 'projects',
    description: 'Управление проектами'
)]
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

    #[OA\Get(
        path: '/project',
        description: 'Возвращает список проектов текущего пользователя',
        summary: 'Список проектов пользователя',
        security: [['bearerAuth' => []]],
        tags: ['projects'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Project')
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

    #[OA\Get(
        path: '/project/{id}',
        summary: 'Получить проект по ID',
        security: [['bearerAuth' => []]],
        tags: ['projects'],
        parameters: [
            new OA\Parameter(
                name: 'id',
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
                            property: 'item',
                            ref: '#/components/schemas/Project'
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
    public function actionView(int $id): ItemDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            throw new UnauthorizedHttpException('User not authenticated');
        }
        $query = new GetProjectQuery($id, $userId);
        try {
            $project = $this->getProjectHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->item($project);
    }

    #[OA\Post(
        path: '/project',
        summary: 'Создать новый проект',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateProjectRequest')
        ),
        tags: ['projects'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Проект создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Project'
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

    #[OA\Put(
        path: '/project/{id}',
        summary: 'Обновить проект',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateProjectRequest')
        ),
        tags: ['projects'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID проекта',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Проект обновлён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Project'
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

    #[OA\Delete(
        path: '/project/{id}',
        summary: 'Удалить проект',
        security: [['bearerAuth' => []]],
        tags: ['projects'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID проекта',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Проект удалён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Project deleted')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Проект не найден')
        ]
    )]
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