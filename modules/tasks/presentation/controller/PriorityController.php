<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\presentation\controller\BaseController;
use modules\tasks\application\handler\ListPrioritiesHandler;
use modules\tasks\application\query\ListPrioritiesQuery;

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

    public function actionIndex(): CollectionDto|array
    {
        $query = new ListPrioritiesQuery();
        $priorities = $this->listPrioritiesHandler->handle($query);
        return $this->collection($priorities);
    }
}