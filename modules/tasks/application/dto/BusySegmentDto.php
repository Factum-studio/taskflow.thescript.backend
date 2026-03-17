<?php

namespace modules\tasks\application\dto;

class BusySegmentDto
{
    public string $start; // 'H:i' или 'Y-m-d H:i:s' в зависимости от детализации
    public string $end;
    public ?array $taskIds = []; // какие задачи пересекаются
    public ?array $anchorPoints = []; // промежуточные якорные значения (для детализации)

    //TODO заменить на array
    public function __construct(
        string $start,
        string $end,
        ?array $taskIds = null,
        ?array $anchorPoints = null
    ) {
        $this->start        = $start;
        $this->end          = $end;
        $this->taskIds      = $taskIds;
        $this->anchorPoints = $anchorPoints;
    }
}