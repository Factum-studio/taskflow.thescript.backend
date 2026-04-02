<?php
namespace core\presentation\controller;

use core\application\command\UpdateCompanyCommand;
use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\application\handler\GetAllCompaniesHandler;
use core\application\handler\GetCompanyByIdHandler;
use core\application\handler\GetCompanyUsersHandler;
use core\application\handler\UpdateCompanyHandler;
use core\application\query\GetAllCompaniesQuery;
use core\application\query\GetCompanyByIdQuery;
use core\application\query\GetCompanyUsersQuery;
use OpenApi\Attributes as OA;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

#[OA\Tag(
    name: 'companies',
    description: 'Управление компаниями'
)]
class CompanyController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly GetAllCompaniesHandler $getAllHandler,
        private readonly GetCompanyByIdHandler $getByIdHandler,
        private readonly GetCompanyUsersHandler $getUsersHandler,
        private readonly UpdateCompanyHandler $updateHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/company',
        summary: 'Список всех компаний',
        security: [['bearerAuth' => []]],
        tags: ['companies'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/Company')),
                        new OA\Property(property: '_meta', ref: '#/components/schemas/Collection/properties/_meta')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация')
        ]
    )]
    public function actionIndex(): CollectionDto|array
    {
        $query = new GetAllCompaniesQuery();
        $companies = $this->getAllHandler->handle($query);
        return $this->collection($companies);
    }

    /**
     * @throws NotFoundHttpException
     */
    #[OA\Get(
        path: '/company/{id}',
        summary: 'Получить компанию по ID',
        security: [['bearerAuth' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Company')),
            new OA\Response(response: 404, description: 'Компания не найдена')
        ]
    )]
    public function actionView(int $id): ItemDto|array
    {
        $query = new GetCompanyByIdQuery($id);
        try {
            $company = $this->getByIdHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->item($company);
    }

    /**
     * @throws NotFoundHttpException
     */
    #[OA\Get(
        path: '/company/{id}/user',
        summary: 'Пользователи компании',
        security: [['bearerAuth' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список пользователей',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/CompanyUser')),
                        new OA\Property(property: '_meta', ref: '#/components/schemas/Collection/properties/_meta')
                    ]
                )
            )
        ]
    )]
    public function actionUsers(int $id): ErrorDto|CollectionDto|array
    {
        $query = new GetCompanyUsersQuery($id);
        $userToken = $this->getUserIdentity()->getJwtToken();
        if (!$userToken) {
            return $this->error('User not authenticated', 401);
        }
        try {
            $users = $this->getUsersHandler->handle($query, $userToken);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
        return $this->collection($users);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Put(
        path: '/company/{id}',
        summary: 'Обновить данные компании',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string')
                ]
            )
        ),
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Обновлено'),
            new OA\Response(response: 404, description: 'Компания не найдена'),
            new OA\Response(response: 403, description: 'Недостаточно прав')
        ]
    )]
    public function actionUpdate(int $id): ErrorDto|SuccessDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $request = Yii::$app->request;
        $name = $request->post('name');
        $description = $request->post('description');

        if (!$name && $description === null) {
            return $this->error('No fields to update', 422);
        }

        $command = new UpdateCompanyCommand($id, $name, $description, $userId);
        try {
            $this->updateHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'core');
            throw new ServerErrorHttpException('Failed to update company', 0, $e);
        }

        return $this->success(null, 'Company updated');
    }
}