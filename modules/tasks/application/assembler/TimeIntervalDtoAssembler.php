<?php

namespace modules\tasks\application\assembler;

use modules\tasks\application\dto\TimeIntervalDto;
use modules\tasks\domain\entity\TimeInterval;

class TimeIntervalDtoAssembler
{
    public function toDto(TimeInterval $interval): TimeIntervalDto
    {
        return new TimeIntervalDto([
            'id'        => $interval->getId()->value(),
            'taskId'    => $interval->getTaskId()->value(),
            'userId'    => $interval->getUserId()->value(),
            'startTime' => $interval->getStartTime()->format('Y-m-d H:i:s'),
            'endTime'   => $interval->getEndTime()?->format('Y-m-d H:i:s'),
            'duration'  => $interval->getDurationSeconds(),
            'comment'   => $interval->getComment(),
            'createdAt' => $interval->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $interval->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param TimeInterval[] $intervals
     * @return TimeIntervalDto[]
     */
    public function toDtoList(array $intervals): array
    {
        return array_map([$this, 'toDto'], $intervals);
    }
}