<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use InvalidArgumentException;
use modules\tasks\application\command\AddCommentCommand;
use modules\tasks\application\command\DeleteCommentCommand;
use modules\tasks\application\command\UpdateCommentCommand;
use modules\tasks\application\handler\AddCommentHandler;
use modules\tasks\application\handler\DeleteCommentHandler;
use modules\tasks\application\handler\GetCommentHandler;
use modules\tasks\application\handler\ListCommentsHandler;
use modules\tasks\application\handler\UpdateCommentHandler;
use modules\tasks\application\query\GetCommentQuery;
use modules\tasks\application\query\ListCommentsQuery;
use modules\tasks\presentation\request\AddCommentRequest;
use modules\tasks\presentation\request\UpdateCommentRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'comments',
    description: 'Управление комментариями к задачам'
)]
class CommentController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly ListCommentsHandler $listCommentsHandler,
        private readonly GetCommentHandler $getCommentHandler,
        private readonly AddCommentHandler $addCommentHandler,
        private readonly UpdateCommentHandler $updateCommentHandler,
        private readonly DeleteCommentHandler $deleteCommentHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/task/{taskId}/comment',
        description: 'Возвращает все комментарии, привязанные к указанной задаче',
        summary: 'Список комментариев задачи',
        security: [['bearerAuth' => []]],
        tags: ['comments'],
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
                            items: new OA\Items(ref: '#/components/schemas/Comment')
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
    public function actionIndex(int $taskId): CollectionDto|array
    {
        $query = new ListCommentsQuery($taskId);
        try {
            $comments = $this->listCommentsHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->collection($comments);
    }

    #[OA\Get(
        path: '/comment/{id}',
        summary: 'Получить комментарий по ID',
        security: [['bearerAuth' => []]],
        tags: ['comments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID комментария',
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
                            ref: '#/components/schemas/Comment'
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
                description: 'Комментарий не найден',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): ItemDto|array
    {
        $query = new GetCommentQuery($id);
        try {
            $comment = $this->getCommentHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->item($comment);
    }

    #[OA\Post(
        path: '/task/{taskId}/comment',
        summary: 'Добавить комментарий к задаче',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AddCommentRequest')
        ),
        tags: ['comments'],
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
                description: 'Комментарий добавлен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Comment'
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
            )
        ]
    )]
    /**
     * @throws ServerErrorHttpException
     * @throws NotFoundHttpException
     */
    public function actionCreate(int $taskId): ItemDto|ErrorDto|array
    {
        $request = new AddCommentRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new AddCommentCommand($taskId, $userId, $request->content);

        try {
            $commentDto = $this->addCommentHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to add comment', 0, $e);
        }

        return $this->item($commentDto);
    }

    #[OA\Put(
        path: '/comment/{id}',
        summary: 'Обновить комментарий',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateCommentRequest')
        ),
        tags: ['comments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID комментария',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Комментарий обновлён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/Comment'
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
                response: 403,
                description: 'Доступ запрещён (не автор)',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Комментарий не найден',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdate(int $id): ItemDto|ErrorDto|array
    {
        $request = new UpdateCommentRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new UpdateCommentCommand($id, $userId, $request->content);

        try {
            $commentDto = $this->updateCommentHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to update comment', 0, $e);
        }

        return $this->item($commentDto);
    }

    #[OA\Delete(
        path: '/comment/{id}',
        summary: 'Удалить комментарий',
        security: [['bearerAuth' => []]],
        tags: ['comments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID комментария',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Комментарий удалён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'null'),
                        new OA\Property(property: 'message', type: 'string', example: 'Comment deleted')
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
                response: 403,
                description: 'Доступ запрещён (не автор)',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Комментарий не найден',
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
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new DeleteCommentCommand($id, $userId);

        try {
            $this->deleteCommentHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to delete comment', 0, $e);
        }

        return $this->success(null, 'Comment deleted');
    }
}