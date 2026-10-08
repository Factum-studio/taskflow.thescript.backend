<?php

declare(strict_types=1);

namespace modules\feedback\application\handler;

use core\application\dto\UserFiltersDto;
use modules\feedback\application\dto\FeedbackStatsDto;
use modules\feedback\application\port\IFeedbackIdeaRepository;
use modules\feedback\application\port\IFeedbackRatingRepository;
use core\application\port\IUserRepository;
use core\application\port\ITaskStatisticsService;
use modules\feedback\application\query\GetFeedbackStatsQuery;

class GetFeedbackStatsHandler
{
    public function __construct(
        private IFeedbackIdeaRepository $ideaRepository,
        private IFeedbackRatingRepository $ratingRepository,
        private IUserRepository $userRepository,
        private ITaskStatisticsService $taskService,
    ) {
    }

    public function handle(GetFeedbackStatsQuery $query): FeedbackStatsDto
    {
        $totalIdeas = $this->ideaRepository->countAll();
        $maxRatings = $this->ratingRepository->countMaxRatings();
        $implementedIdeas = $this->ideaRepository->countImplemented();
        $minRatings = $this->ratingRepository->countMinRatings();
        $totalUsers = $this->userRepository->countWithFilters(new UserFiltersDto()); // TODO: relocate to method countAll
        $totalTasks = $this->taskService->getTotalTasks();

        return new FeedbackStatsDto(
            $totalIdeas,
            $maxRatings,
            $implementedIdeas,
            $minRatings,
            $totalUsers,
            $totalTasks,
        );
    }
}
