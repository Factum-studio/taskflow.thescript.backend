<?php
namespace core\application\handler;

use core\application\dto\FeedbackStatsDto;
use core\application\port\IFeedbackIdeaRepository;
use core\application\port\IFeedbackRatingRepository;
use core\application\port\ILocalUserRepository;
use core\application\port\ITaskStatisticsService;
use core\application\query\GetFeedbackStatsQuery;

class GetFeedbackStatsHandler
{
    public function __construct(
        private IFeedbackIdeaRepository $ideaRepository,
        private IFeedbackRatingRepository $ratingRepository,
        private ILocalUserRepository $userRepository,
        private ITaskStatisticsService $taskService
    ) {}

    public function handle(GetFeedbackStatsQuery $query): FeedbackStatsDto
    {
        $totalIdeas = $this->ideaRepository->countAll();
        $maxRatings = $this->ratingRepository->countMaxRatings();
        $implementedIdeas = $this->ideaRepository->countImplemented();
        $minRatings = $this->ratingRepository->countMinRatings();
        $totalUsers = $this->userRepository->countAll();
        $totalTasks = $this->taskService->getTotalTasks();

        return new FeedbackStatsDto(
            $totalIdeas,
            $maxRatings,
            $implementedIdeas,
            $minRatings,
            $totalUsers,
            $totalTasks
        );
    }
}