<?php

declare(strict_types=1);

namespace modules\feedback\application\dto;

use JsonSerializable;

class FeedbackStatsDto implements JsonSerializable
{
    public function __construct(
        public int $totalIdeas,
        public int $maxRatings,
        public int $implementedIdeas,
        public int $minRatings,
        public int $totalUsers,
        public int $totalTasks,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'totalIdeas'        => $this->totalIdeas,
            'maxRatings'        => $this->maxRatings,
            'implementedIdeas'  => $this->implementedIdeas,
            'minRatings'        => $this->minRatings,
            'totalUsers'        => $this->totalUsers,
            'totalTasks'        => $this->totalTasks,
        ];
    }
}
