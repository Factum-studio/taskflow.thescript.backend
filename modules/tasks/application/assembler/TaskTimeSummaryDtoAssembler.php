<?php

namespace modules\tasks\application\assembler;

use DateTimeImmutable;
use Exception;
use modules\tasks\application\dto\BusySegmentDto;
use modules\tasks\application\dto\TaskTimeSummaryDto;
use modules\tasks\application\dto\TimeBlockDto;

class TaskTimeSummaryDtoAssembler
{
    /**
     * @param array $summary - результат работы BusyChartService::buildTaskSummary()
     * @return TaskTimeSummaryDto
     * @throws Exception
     */
    public function toDto(array $summary): TaskTimeSummaryDto
    {
        $blockDtos = [];
        foreach ($summary['blocks'] as $segment) {
            if (!$segment instanceof BusySegmentDto) {
                continue;
            }
            $start = $segment->start;
            $end = $segment->end;

            $blockDtos[] = new TimeBlockDto([
                'start'     => $start,
                'end'       => $end,
                'type'      => $segment->type ?? null,
                'taskIds'   => $segment->taskIds ?? null,
                'duration'  => $segment->duration ?? (new DateTimeImmutable($end))->getTimestamp() - (new DateTimeImmutable($start))->getTimestamp(),
            ]);
        }

        return new TaskTimeSummaryDto([
            'taskId'        => $summary['taskId'],
            'taskTitle'     => $summary['taskTitle'],
            'blocks'        => $blockDtos,
            'totalDuration' => $summary['totalDuration'],
        ]);
    }
}