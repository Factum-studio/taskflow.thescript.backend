<?php

declare(strict_types=1);

namespace core\presentation\controller;

use core\application\command\SyncUserCommand;
use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\application\handler\GetUserHandler;
use core\application\handler\ListUserHandler;
use core\application\handler\SyncUserHandler;
use core\application\port\IUserRepository;
use modules\passport\auth\application\port\PassportAuthPort;
use OpenApi\Attributes as OA;
use core\application\query\GetUserQuery;
use core\application\query\ListUserQuery;
use core\application\dto\UserFiltersDto;
use core\domain\exception\UserNotFoundException;
use core\domain\exception\ValidationException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;

#[OA\Tag(
    name: 'users',
    description: 'Управление пользователями',
)]
class UserController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly PassportAuthPort $passport,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }
    #[OA\Get(
        path: '/users/{id}',
        summary: 'Получение пользователя по ID',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Item')),
            new OA\Response(response: 404, description: 'Пользователь не найден'),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws ValidationException
     * @throws UserNotFoundException
     */
    public function actionView(int $id): ItemDto
    {
        $query = new GetUserQuery($id);
        $handler = Yii::$container->get(GetUserHandler::class);
        try {
            $dto = $handler->handle($query);
        } catch (UserNotFoundException $e) {
            throw new UserNotFoundException($e->getMessage());
        }
        return $this->item($dto);
    }

    #[OA\Get(
        path: '/users',
        summary: 'Список пользователей с фильтрацией',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['users'],
        parameters: [
            new OA\Parameter(name: 'ids', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'offset', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'orderBy', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'email', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'surname', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'patronymic', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'isOwner', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'passportId', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'post', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'createdFrom', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'createdTo', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'updatedFrom', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'updatedTo', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'syncFrom', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'syncTo', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Collection')),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function actionIndex(): CollectionDto
    {
        $params = Yii::$app->request->get();
        $filters = new UserFiltersDto(
            ids: $params['ids'] ?? null,
            limit: $this->getLimit(),
            offset: isset($params['offset']) ? (int)$params['offset'] : null,
            orderBy: $params['orderBy'] ?? null,
            email: $params['email'] ?? null,
            surname: $params['surname'] ?? null,
            name: $params['name'] ?? null,
            patronymic: $params['patronymic'] ?? null,
            isOwner: isset($params['isOwner']) ? filter_var($params['isOwner'], FILTER_VALIDATE_BOOLEAN) : null,
            passportId: isset($params['passportId']) ? (int)$params['passportId'] : null,
            post: $params['post'] ?? null,
            syncFrom: $params['syncFrom'] ?? null,
            syncTo: $params['syncTo'] ?? null,
            createdFrom: $params['createdFrom'] ?? null,
            createdTo: $params['createdTo'] ?? null,
            updatedFrom: $params['updatedFrom'] ?? null,
            updatedTo: $params['updatedTo'] ?? null,
        );

        $query = new ListUserQuery($filters);
        $handler = Yii::$container->get(ListUserHandler::class);
        $items = $handler->handle($query);

        $total = Yii::$container->get(IUserRepository::class)
            ->countWithFilters($filters);

        return $this->collection(
            $items,
            $total,
            $filters->offset ? (int)($filters->offset / ($filters->limit ?: 1)) + 1 : 1,
            $filters->limit,
        );
    }

    #[OA\Get(
        path: '/users/sync',
        summary: 'Принудительная синхронизация текущего авторизированного пользователя',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['users'],
        responses: [
            new OA\Response(response: 200, description: 'Успешно', content: new OA\JsonContent(ref: '#/components/schemas/Success')),
            new OA\Response(response: 404, description: 'Пользователь не найден'),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws ValidationException
     * @throws Throwable
     */
    public function actionSync(): SuccessDto|ErrorDto
    {
        $passportId = $this->getUserId();
        $token = Yii::$app->request->cookies->getValue("taskflow_access_token");
        if (!is_string($token) || $token === '') {
            return $this->error("Token not found.");
        }
        $user = $this->passport->getUser($passportId, $token);
        $command = new SyncUserCommand(
            passportId: (int)$user->id,
            surname: $user->surname,
            name: $user->name,
            email: $user->email,
            isOwner: $user->isOwner,
            patronymic: $user->patronymic,
            post: $user->post,
        );
        $handler = Yii::$container->get(SyncUserHandler::class);
        $handler->handle($command);
        return $this->success();
    }
}
