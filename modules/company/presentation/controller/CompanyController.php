<?php

declare(strict_types=1);

namespace modules\company\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\domain\exception\ValidationException;
use core\presentation\controller\BaseController;
use modules\company\application\command\ArchiveCompanyCommand;
use modules\company\application\command\CreateCompanyCommand;
use modules\company\application\command\DeleteCompanyCommand;
use modules\company\application\command\UnarchiveCompanyCommand;
use modules\company\application\command\UpdateCompanyCommand;
use modules\company\application\command\VerifyCompanyCommand;
use modules\company\application\handler\ArchiveCompanyHandler;
use modules\company\application\handler\CreateCompanyHandler;
use modules\company\application\handler\DeleteCompanyHandler;
use modules\company\application\handler\GetCompanyHandler;
use modules\company\application\handler\ListCompaniesHandler;
use modules\company\application\handler\UnarchiveCompanyHandler;
use modules\company\application\handler\UpdateCompanyHandler;
use modules\company\application\handler\VerifyCompanyHandler;
use modules\company\application\query\GetCompanyQuery;
use modules\company\application\query\ListCompaniesQuery;
use modules\company\presentation\request\CreateCompanyRequest;
use modules\company\presentation\request\UpdateCompanyRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use OpenApi\Attributes as OA;
use yii\web\UnauthorizedHttpException;

#[OA\Tag(
    name: 'companies',
    description: 'Управление компаниями',
)]
class CompanyController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly CreateCompanyHandler $createCompanyHandler,
        private readonly UpdateCompanyHandler $updateCompanyHandler,
        private readonly DeleteCompanyHandler $deleteCompanyHandler,
        private readonly ArchiveCompanyHandler $archiveCompanyHandler,
        private readonly UnarchiveCompanyHandler $unarchiveCompanyHandler,
        private readonly VerifyCompanyHandler $verifyCompanyHandler,
        private readonly GetCompanyHandler $getCompanyHandler,
        private readonly ListCompaniesHandler $listCompaniesHandler,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/company',
        description: 'Возвращает список компаний с фильтрами и пагинацией',
        summary: 'Список компаний',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'archived'])),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешный ответ'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
        ],
    )]
    public function actionIndex(): CollectionDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $query = new ListCompaniesQuery(
            userId: (int)$userId,
            search: Yii::$app->request->get('search'),
            status: Yii::$app->request->get('status'),
            page: $this->getPage(),
            limit: $this->getLimit(),
        );

        $companies = $this->listCompaniesHandler->handle($query);
        $total = $this->listCompaniesHandler->count($query);

        return $this->collection($companies, $total, $query->page, $query->limit);
    }

    /**
     * @throws UnauthorizedHttpException
     * @throws ValidationException
     * @throws NotFoundHttpException
     */
    #[OA\Get(
        path: '/company/{id}',
        summary: 'Получить компанию по ID',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешный ответ'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Компания не найдена'),
        ],
    )]
    public function actionView(int $id): ItemDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            throw new UnauthorizedHttpException('User not authenticated');
        }

        $query = new GetCompanyQuery($id, (int)$userId);
        try {
            $company = $this->getCompanyHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->item($company);
    }

    /**
     * @throws ServerErrorHttpException
     */
    #[OA\Post(
        path: '/company',
        summary: 'Создать новую компанию',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateCompanyRequest'),
        ),
        tags: ['companies'],
        responses: [
            new OA\Response(response: 200, description: 'Компания создана'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
    public function actionCreate(): ItemDto|ErrorDto|array
    {
        $request = new CreateCompanyRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new CreateCompanyCommand(
            name: $request->name,
            ownerId: (int)$userId,
            email: $request->email,
            website: $request->website,
            phone: $request->phone,
            country: $request->country,
            city: $request->city,
            address: $request->address,
            taxNum: $request->taxNum,
            registrationNum: $request->registrationNum,
            description: $request->description,
        );

        try {
            $companyDto = $this->createCompanyHandler->handle($command);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to create company', 0, $e);
        }

        return $this->item($companyDto);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Put(
        path: '/company/{id}',
        summary: 'Обновить данные компании',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateCompanyRequest'),
        ),
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Компания обновлена'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Компания не найдена'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
    public function actionUpdate(int $id): ErrorDto|ItemDto|array
    {
        $request = new UpdateCompanyRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new UpdateCompanyCommand(
            id: $id,
            updatedBy: (int)$userId,
            name: $request->name,
            email: $request->email,
            website: $request->website,
            phone: $request->phone,
            country: $request->country,
            city: $request->city,
            address: $request->address,
            taxNum: $request->taxNum,
            registrationNum: $request->registrationNum,
            description: $request->description,
        );

        try {
            $companyDto = $this->updateCompanyHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to update company', 0, $e);
        }

        return $this->item($companyDto);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Post(
        path: '/company/{id}/archive',
        summary: 'Архивировать компанию',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Компания архивирована'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Компания не найдена'),
        ],
    )]
    public function actionArchive(int $id): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new ArchiveCompanyCommand($id, (int)$userId);

        try {
            $this->archiveCompanyHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            return $this->error($e->getMessage(), 403);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to archive company', 0, $e);
        }

        return $this->success(null, 'Company archived');
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Post(
        path: '/company/{id}/unarchive',
        summary: 'Разархивировать компанию',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Компания разархивирована'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Компания не найдена'),
        ],
    )]
    public function actionUnarchive(int $id): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new UnarchiveCompanyCommand($id, (int)$userId);

        try {
            $this->unarchiveCompanyHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            return $this->error($e->getMessage(), 403);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to unarchive company', 0, $e);
        }

        return $this->success(null, 'Company unarchived');
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Post(
        path: '/company/{id}/verify',
        summary: 'Верифицировать компанию (только для глобальных админов)',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Компания верифицирована'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Компания не найдена'),
        ],
    )]
    public function actionVerify(int $id): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new VerifyCompanyCommand($id, (int)$userId);

        try {
            $this->verifyCompanyHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to verify company', 0, $e);
        }

        return $this->success(null, 'Company verified');
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Delete(
        path: '/company/{id}',
        summary: 'Удалить компанию (только для глобальных админов)',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Компания удалена'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Компания не найдена'),
        ],
    )]
    public function actionDelete(int $id): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new DeleteCompanyCommand($id, (int)$userId);

        try {
            $this->deleteCompanyHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to delete company', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to delete company', 0, $e);
        }

        return $this->success(null, 'Company deleted');
    }
}
