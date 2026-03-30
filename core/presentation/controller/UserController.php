<?php

namespace core\presentation\controller;

use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\UserDto;
use core\application\handler\GetCurrentUserQueryHandler;
use core\application\query\GetCurrentUserQuery;
use core\domain\exception\UserNotFoundException;
use OpenApi\Attributes as OA;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
//TODO убрать, это временное решение
use yii\httpclient\Client;

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

    #[OA\Post(
        path: '/user/login',
        description: 'Аутентификация пользователя через Passport',
        summary: 'Вход в систему',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password'],
                properties: [
                    new OA\Property(property: 'login', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ],
                type: 'object'
            )
        ),
        tags: ['users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешная аутентификация',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
                        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Неверные учетные данные',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function actionLogin(): ErrorDto|array
    {
        $request = Yii::$app->request;

        $login = $request->post('login');
        $password = $request->post('password');

        if (empty($login) || empty($password)) {
            return $this->error('Login and password are required', 422);
        }

        $httpClient = new Client();

        try {
            $response = $httpClient->createRequest()
                ->setMethod('POST')
                ->setUrl('https://resume.thescript.agency/api/passport/v1/auth/login')
                ->setData([
                    'login' => $login,
                    'password' => $password,
                ])
                ->setFormat(Client::FORMAT_JSON)
                ->send();

            $statusCode = $response->getStatusCode();
            $data = $response->getData();

            Yii::$app->response->statusCode = $statusCode;

            return $data;

        } catch (\Exception $e) {
            Yii::error('Passport login failed: ' . $e->getMessage(), 'auth');
            return $this->error('Authentication service unavailable', 503);
        }
    }
}