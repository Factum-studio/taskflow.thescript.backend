<?php
namespace core\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\handler\GetAllAuthorsHandler;
use core\application\query\GetAllAuthorsQuery;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'authors',
    description: 'Авторы системы'
)]
class AuthorController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly GetAllAuthorsHandler $getAllHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/authors',
        summary: 'Список всех авторов системы',
        security: [['bearerAuth' => []]],
        tags: ['authors'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/Author')),
                        new OA\Property(property: '_meta', ref: '#/components/schemas/Collection/properties/_meta')
                    ]
                )
            )
        ]
    )]
    public function actionIndex(): ErrorDto|CollectionDto|array
    {
        $userIdentity = $this->getUserIdentity();
        if (!$userIdentity) {
            return $this->error('User not authenticated', 401);
        }
        $authors = $this->getAllHandler->handle(new GetAllAuthorsQuery(), $userIdentity->getJwtToken());
        return $this->collection($authors);
    }
}