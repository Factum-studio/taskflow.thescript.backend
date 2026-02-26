<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\presentation\controller\BaseController;
use modules\tasks\application\handler\ListPrioritiesHandler;
use modules\tasks\application\query\ListPrioritiesQuery;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'task-priorities',
    description: 'Справочник приоритетов задач'
)]
class PriorityController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly ListPrioritiesHandler $listPrioritiesHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/task-priority',
        summary: 'Список приоритетов задач',
        security: [['bearerAuth' => []]],
        tags: ['task-priorities'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/TaskPriority')
                        ),
                        new OA\Property(
                            property: '_meta',
                            ref: '#/components/schemas/Collection/properties/_meta'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация')
        ]
    )]
    public function actionIndex(): CollectionDto|array
    {
        $query = new ListPrioritiesQuery();
        $priorities = $this->listPrioritiesHandler->handle($query);
        return $this->collection($priorities);
    }
}