<?php

namespace core\presentation\controller;

use core\application\dto\ItemDto;
use core\application\dto\UserDto;
use core\application\handler\GetCurrentUserQueryHandler;
use core\application\query\GetCurrentUserQuery;
use core\domain\exception\UserNotFoundException;
use OpenApi\Attributes as OA;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

#[OA\Tag(
    name: 'users',
    description: 'Управление пользователями'
)]
final class UserController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly GetCurrentUserQueryHandler $getCurrentUserHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/user/me',
        description: 'Возвращает информацию о текущем аутентифицированном пользователе',
        summary: 'Получить данные текущего пользователя',
        security: [['bearerAuth' => []]],
        tags: ['users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный запрос',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/User'
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
                description: 'Пользователь не найден',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function actionMe(): ItemDto
    {
        try {
            $user = $this->getCurrentUserHandler->handle(new GetCurrentUserQuery());

            return $this->item(UserDto::fromEntity($user));

        } catch (UserNotFoundException $e) {
            throw new NotFoundHttpException('User not found');
        }
    }
}