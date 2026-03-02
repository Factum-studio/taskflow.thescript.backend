<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\DailySummaryDtoAssembler;
use modules\tasks\application\query\GetDailySummaryQuery;
use modules\tasks\domain\repository\IDailySummaryRepository;
use modules\tasks\domain\valueObject\Date;
use modules\tasks\domain\valueObject\UserId;

class GetDailySummaryHandler
{
    private IDailySummaryRepository $dailySummaryRepository;
    private DailySummaryDtoAssembler $dtoAssembler;

    public function __construct(
        IDailySummaryRepository $dailySummaryRepository,
        DailySummaryDtoAssembler $dtoAssembler
    ) {
        $this->dailySummaryRepository   = $dailySummaryRepository;
        $this->dtoAssembler             = $dtoAssembler;
    }

    public function handle(GetDailySummaryQuery $query): array
    {
        $userId = new UserId($query->userId);
        $date = Date::fromString($query->date->format('Y-m-d'));

        $summaries = $this->dailySummaryRepository->findByUserAndDate($userId, $date);
        return $this->dtoAssembler->toDtoList($summaries);
    }
}