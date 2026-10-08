<?php

// core/presentation/controller/UserRoleController.php

declare(strict_types=1);

namespace core\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\SuccessDto;
use core\application\handler\AssignUserRoleHandler;
use core\application\handler\ListUserRoleHandler;
use core\application\handler\RemoveUserRoleHandler;
use core\application\port\IUserRoleSearch;
use core\domain\exception\EntityUnavailableException;
use core\domain\exception\UserNotFoundException;
use OpenApi\Attributes as OA;
use core\application\command\AssignUserRoleCommand;
use core\application\command\RemoveUserRoleCommand;
use core\application\query\ListUserRoleQuery;
use core\application\dto\UserRoleFiltersDto;
use core\presentation\request\UserRoleAssignRequest;
use core\domain\exception\EntityNotFoundException;
use core\domain\exception\ValidationException;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;

#[OA\Tag(
    name: 'user-roles',
    description: 'Роли пользователя',
)]
class UserRoleController extends BaseController
{
    #[OA\Post(
        path: '/user-roles',
        summary: 'Назначение роли пользователю',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UserRoleAssignRequest'),
        ),
        tags: ['user-roles'],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Success')),
            new OA\Response(response: 400, description: 'Ошибка валидации'),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @throws UserNotFoundException
     * @throws EntityUnavailableException
     */
    public function actionCreate(): SuccessDto
    {
        $request = new UserRoleAssignRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            throw new ValidationException('Validation failed: ', $request->getErrors());
        }

        $command = new AssignUserRoleCommand(
            userId: $request->userId,
            roleId: $request->roleId,
            assignedBy: $this->getUserId() !== null ? (int)$this->getUserId() : null,
        );
        $handler = Yii::$container->get(AssignUserRoleHandler::class);
        try {
            $handler->handle($command);
        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        } catch (EntityNotFoundException $e) {
            throw new EntityNotFoundException($e->getMessage());
        }
        return $this->success(null, 'Role assigned');
    }

    #[OA\Delete(
        path: '/user-roles/{id}',
        summary: 'Удаление назначения роли',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['user-roles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Success')),
            new OA\Response(response: 404, description: 'Назначение не найдено'),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws UserNotFoundException
     * @throws EntityUnavailableException
     * @throws EntityNotFoundException
     */
    public function actionDelete(int $id): SuccessDto
    {
        $command = new RemoveUserRoleCommand($id);
        $handler = Yii::$container->get(RemoveUserRoleHandler::class);
        try {
            $handler->handle($command);
        } catch (EntityNotFoundException $e) {
            throw new EntityNotFoundException($e->getMessage());
        }
        return $this->success(null, 'User role removed');
    }

    #[OA\Get(
        path: '/user-roles',
        summary: 'Список назначений ролей с фильтрацией',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['user-roles'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'offset', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'orderBy', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'userId', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'roleId', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'assignedBy', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'assignedFrom', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'assignedTo', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Collection')),
        ],
    )]
    public function actionIndex(): CollectionDto
    {
        $params = Yii::$app->request->get();
        $filters = new UserRoleFiltersDto(
            ids: $params['ids'] ?? null,
            limit: $this->getLimit(),
            offset: isset($params['offset']) ? (int)$params['offset'] : null,
            orderBy: $params['orderBy'] ?? null,
            userId: isset($params['userId']) ? (int)$params['userId'] : null,
            roleId: isset($params['roleId']) ? (int)$params['roleId'] : null,
            assignedFrom: $params['assignedFrom'] ?? null,
            assignedTo: $params['assignedTo'] ?? null,
            assignedBy: isset($params['assignedBy']) ? (int)$params['assignedBy'] : null,
        );

        $query = new ListUserRoleQuery($filters);
        $handler = Yii::$container->get(ListUserRoleHandler::class);
        $items = $handler->handle($query);

        $total = Yii::$container->get(IUserRoleSearch::class)
            ->countWithFilters($filters);

        return $this->collection(
            $items,
            $total,
            $filters->offset ? (int)($filters->offset / ($filters->limit ?: 1)) + 1 : 1,
            $filters->limit,
        );
    }
}
