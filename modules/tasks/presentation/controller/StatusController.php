<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\presentation\controller\BaseController;
use modules\tasks\application\handler\ListStatusesHandler;
use modules\tasks\application\query\ListStatusesQuery;

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

    public function actionIndex(): CollectionDto|array
    {
        $query = new ListStatusesQuery();
        $statuses = $this->listStatusesHandler->handle($query);
        return $this->collection($statuses);
    }
}