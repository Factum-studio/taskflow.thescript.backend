<?php

namespace modules\projects\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use modules\projects\application\command\AddProjectMemberCommand;
use modules\projects\application\command\ChangeMemberRoleCommand;
use modules\projects\application\command\RemoveProjectMemberCommand;
use modules\projects\application\handler\AddProjectMemberHandler;
use modules\projects\application\handler\ChangeMemberRoleHandler;
use modules\projects\application\handler\ListProjectMembersHandler;
use modules\projects\application\handler\RemoveProjectMemberHandler;
use modules\projects\application\query\ListProjectMembersQuery;
use modules\projects\presentation\request\AddProjectMemberRequest;
use modules\projects\presentation\request\ChangeProjectMemberRoleRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use OpenApi\Attributes as OA;
use yii\web\UnauthorizedHttpException;

#[OA\Tag(
    name: 'project-members',
    description: 'Управление участниками проекта'
)]
class ProjectMemberController extends BaseController
{
    public function __construct(
                                                    $id,
                                                    $module,
        private readonly AddProjectMemberHandler    $addMemberHandler,
        private readonly RemoveProjectMemberHandler $removeMemberHandler,
        private readonly ChangeMemberRoleHandler    $changeRoleHandler,
        private readonly ListProjectMembersHandler  $listMembersHandler,
                                                    $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/project/{projectId}/member',
        description: 'Возвращает список участников проекта',
        summary: 'Участники проекта',
        security: [['bearerAuth' => []]],
        tags: ['project-members'],
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
                            items: new OA\Items(ref: '#/components/schemas/ProjectUser')
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
        $query = new ListProjectMembersQuery($projectId, $userId);
        try {
            $members = $this->listMembersHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->collection($members);
    }

    #[OA\Post(
        path: '/project/{projectId}/member',
        summary: 'Добавить участника в проект',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AddProjectMemberRequest')
        ),
        tags: ['project-members'],
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
                description: 'Участник добавлен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/ProjectUser'
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
        $request = new AddProjectMemberRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $identity = $this->getUserIdentity();
        $jwtToken = $identity?->getJwtToken()?->value() ?? '';

        $command = new AddProjectMemberCommand(
            projectId: $projectId,
            userId: $request->userId,
            role: $request->role,
            addedBy: $userId,
            jwtToken: $jwtToken
        );

        try {
            $memberDto = $this->addMemberHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to add member', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to add member', 0, $e);
        }

        return $this->item($memberDto);
    }

    #[OA\Put(
        path: '/project/{projectId}/member/{userId}/role',
        summary: 'Изменить роль участника',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ChangeProjectMemberRoleRequest')
        ),
        tags: ['project-members'],
        parameters: [
            new OA\Parameter(
                name: 'projectId',
                description: 'ID проекта',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'userId',
                description: 'ID пользователя',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Роль изменена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/ProjectUser'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Проект или участник не найдены'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdateRole(int $projectId, int $userId): ErrorDto|ItemDto|array
    {
        $request = new ChangeProjectMemberRoleRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $currentUserId = $this->getUserId();
        if (!$currentUserId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new ChangeMemberRoleCommand(
            projectId: $projectId,
            userId: $userId,
            newRole: $request->role,
            changedBy: $currentUserId
        );

        try {
            $memberDto = $this->changeRoleHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'not a member')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to change role', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to change role', 0, $e);
        }

        return $this->item($memberDto);
    }

    #[OA\Delete(
        path: '/project/{projectId}/member/{userId}',
        summary: 'Удалить участника из проекта',
        security: [['bearerAuth' => []]],
        tags: ['project-members'],
        parameters: [
            new OA\Parameter(
                name: 'projectId',
                description: 'ID проекта',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'userId',
                description: 'ID пользователя',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Участник удалён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Member removed')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Проект или участник не найдены')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionDelete(int $projectId, int $userId): SuccessDto|ErrorDto|array
    {
        $currentUserId = $this->getUserId();
        if (!$currentUserId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new RemoveProjectMemberCommand($projectId, $userId, $currentUserId);

        try {
            $this->removeMemberHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'not a member')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to remove member', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to remove member', 0, $e);
        }

        return $this->success(null, 'Member removed');
    }
}