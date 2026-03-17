<?php

namespace modules\tasks\application\assembler;

use modules\tasks\application\dto\DailySummaryDto;
use modules\tasks\domain\entity\DailySummary;

class DailySummaryDtoAssembler
{
    public function toDto(DailySummary $summary): DailySummaryDto
    {
        return new DailySummaryDto([
            'id'            => $summary->getId()->getValue(),
            'taskId'        => $summary->getTaskId()->getValue(),
            'userId'        => $summary->getUserId()->getValue(),
            'date'          => $summary->getDate()->toString(),
            'totalDuration' => $summary->getTotalDuration()->getSeconds(),
            'updatedAt'     => $summary->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param DailySummary[] $summaries
     * @return DailySummaryDto[]
     */
    public function toDtoList(array $summaries): array
    {
        return array_map([$this, 'toDto'], $summaries);
    }
}