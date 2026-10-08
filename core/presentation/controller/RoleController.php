<?php

declare(strict_types=1);

namespace core\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\application\handler\CreateRoleHandler;
use core\application\handler\DeleteRoleHandler;
use core\application\handler\GetRoleHandler;
use core\application\handler\ListRoleHandler;
use core\application\handler\UpdateRoleHandler;
use core\application\port\IRoleRepository;
use OpenApi\Attributes as OA;
use core\application\command\CreateRoleCommand;
use core\application\command\UpdateRoleCommand;
use core\application\command\DeleteRoleCommand;
use core\application\query\GetRoleQuery;
use core\application\query\ListRoleQuery;
use core\application\dto\RoleFiltersDto;
use core\presentation\request\RoleRequest;
use core\domain\exception\EntityNotFoundException;
use core\domain\exception\ValidationException;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;

#[OA\Tag(
    name: 'roles',
    description: 'Управление ролями',
)]
class RoleController extends BaseController
{
    #[OA\Post(
        path: '/roles',
        summary: 'Создание роли',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RoleRequest'),
        ),
        tags: ['roles'],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Item')),
            new OA\Response(response: 400, description: 'Ошибка валидации'),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws ValidationException
     */
    public function actionCreate(): ItemDto
    {
        $request = new RoleRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            throw new ValidationException('Validation failed: ', $request->getErrors());
        }

        $command = new CreateRoleCommand($request->name, $request->description);
        $handler = Yii::$container->get(CreateRoleHandler::class);
        $dto = $handler->handle($command);

        return $this->item($dto);
    }

    #[OA\Put(
        path: '/roles/{id}',
        summary: 'Обновление роли',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RoleRequest'),
        ),
        tags: ['roles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Item')),
            new OA\Response(response: 404, description: 'Роль не найдена'),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function actionUpdate(int $id): ItemDto
    {
        $request = new RoleRequest();
        $request->load(Yii::$app->request->getBodyParams(), '');
        if (!$request->validate()) {
            throw new ValidationException('Validation failed: ', $request->getErrors());
        }

        $command = new UpdateRoleCommand($id, $request->name, $request->description);
        $handler = Yii::$container->get(UpdateRoleHandler::class);
        try {
            $dto = $handler->handle($command);
        } catch (EntityNotFoundException $e) {
            throw new EntityNotFoundException($e->getMessage());
        }
        return $this->item($dto);
    }

    #[OA\Delete(
        path: '/roles/{id}',
        summary: 'Удаление роли',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['roles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешно удалено', content: new OA\JsonContent(ref: '#/components/schemas/Success')),
            new OA\Response(response: 404, description: 'Роль не найдена'),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws ValidationException
     */
    public function actionDelete(int $id): SuccessDto
    {
        $command = new DeleteRoleCommand($id);
        $handler = Yii::$container->get(DeleteRoleHandler::class);
        $handler->handle($command);
        return $this->success(null, 'Role deleted');
    }

    #[OA\Get(
        path: '/roles/{id}',
        summary: 'Получение роли по ID',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['roles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Item')),
            new OA\Response(response: 404, description: 'Роль не найдена'),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function actionView(int $id): ItemDto
    {
        $query = new GetRoleQuery($id);
        $handler = Yii::$container->get(GetRoleHandler::class);
        try {
            $dto = $handler->handle($query);
        } catch (EntityNotFoundException $e) {
            throw new EntityNotFoundException($e->getMessage());
        }
        return $this->item($dto);
    }

    #[OA\Get(
        path: '/roles',
        summary: 'Список ролей с фильтрацией',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['roles'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'offset', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'orderBy', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'description', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'createdFrom', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'createdTo', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'ids', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Collection')),
        ],
    )]
    public function actionIndex(): CollectionDto
    {
        $params = Yii::$app->request->get();
        $filters = new RoleFiltersDto(
            ids: $params['ids'] ?? null,
            limit: $this->getLimit(),
            offset: isset($params['offset']) ? (int)$params['offset'] : null,
            orderBy: $params['orderBy'] ?? null,
            name: $params['name'] ?? null,
            description: $params['description'] ?? null,
            createdFrom: $params['createdFrom'] ?? null,
            createdTo: $params['createdTo'] ?? null,
        );
        $query = new ListRoleQuery($filters);
        $handler = Yii::$container->get(ListRoleHandler::class);
        $items = $handler->handle($query);

        $total = Yii::$container->get(IRoleRepository::class)
            ->countWithFilters($filters);

        return $this->collection(
            $items,
            $total,
            $filters->offset ? (int)($filters->offset / ($filters->limit ?: 1)) + 1 : 1,
            $filters->limit,
        );
    }
}
