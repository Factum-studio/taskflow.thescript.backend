<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\presentation\controller\BaseController;
use modules\tasks\application\handler\ListStatusesHandler;
use modules\tasks\application\query\ListStatusesQuery;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'task-statuses',
    description: 'Справочник статусов задач'
)]
class StatusController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly ListStatusesHandler $listStatusesHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/task-status',
        summary: 'Список статусов задач',
        security: [['bearerAuth' => []]],
        tags: ['task-statuses'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/TaskStatus')
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
        $query = new ListStatusesQuery();
        $statuses = $this->listStatusesHandler->handle($query);
        return $this->collection($statuses);
    }
}