<?php
namespace core\application\dto;

class FeedbackStatsDto implements \JsonSerializable
{
    public function __construct(
        public int $totalIdeas,
        public int $maxRatings,
        public int $implementedIdeas,
        public int $minRatings,
        public int $totalUsers,
        public int $totalTasks
    ) {}

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