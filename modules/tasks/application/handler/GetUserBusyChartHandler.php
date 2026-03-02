<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\query\GetUserBusyChartQuery;
use modules\tasks\application\service\BusyChartService;
use modules\tasks\domain\repository\ITimeIntervalRepository;
use modules\tasks\domain\valueObject\UserId;

class GetUserBusyChartHandler
{
    private ITimeIntervalRepository $intervalRepository;
    private BusyChartService $chartService;

    public function __construct(
        ITimeIntervalRepository $intervalRepository,
        BusyChartService $chartService
    ) {
        $this->intervalRepository   = $intervalRepository;
        $this->chartService         = $chartService;
    }

    public function handle(GetUserBusyChartQuery $query): array
    {
        $userId = new UserId($query->userId);
        $intervals = $this->intervalRepository->findByUser($userId, $query->from, $query->to);
        return $this->chartService->build($intervals, $query->granularity, $query->mode);
    }
}