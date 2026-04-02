<?php

namespace core\presentation\controller;

use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\application\dto\UserDto;
use core\application\handler\GetCurrentUserQueryHandler;
use core\application\port\ICompanyRepository;
use core\application\query\GetCurrentUserQuery;
use core\domain\entity\Company;
use core\domain\exception\UserNotFoundException;
use core\domain\valueObject\CompanyId;
use core\domain\valueObject\CompanyName;
use core\infrastructure\persistence\UserAR;
use OpenApi\Attributes as OA;
use RuntimeException;
use Throwable;
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
        private readonly ICompanyRepository $companyRepository,
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

    #[OA\Put(
        path: '/user/me/company',
        summary: 'Установить компанию и должность текущего пользователя',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'companyName', type: 'string'),
                    new OA\Property(property: 'post', type: 'string', nullable: true)
                ]
            )
        ),
        tags: ['users'],
        responses: [
            new OA\Response(response: 200, description: 'Обновлено'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    public function actionUpdateCompany(): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $request = Yii::$app->request;
        $companyName = $request->post('companyName');
        $post = $request->post('post');

        if (empty($companyName)) {
            return $this->error('Company name is required', 422);
        }

        try {
            // Поиск или создание компании
            $companyNameObj = new CompanyName($companyName);
            $company = $this->companyRepository->findByName($companyNameObj);
            if (!$company) {
                $company = new Company(
                    new CompanyId(0),
                    $companyNameObj,
                    null
                );
                $company = $this->companyRepository->save($company);
            }

            // Обновление локального пользователя
            $localUser = UserAR::find()->where(['user_id' => $userId])->one();
            if (!$localUser) {
                return $this->error('Local user not found', 404);
            }

            $localUser->company_id = $company->getId()->value();
            $localUser->post = $post;
            if (!$localUser->save()) {
                throw new RuntimeException('Failed to update user company');
            }

        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'user');
            return $this->error('Failed to update company', 500);
        }

        return $this->success(null, 'Company updated');
    }
}