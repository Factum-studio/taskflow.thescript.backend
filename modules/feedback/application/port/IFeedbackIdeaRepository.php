<?php

declare(strict_types=1);

namespace modules\feedback\application\port;

use modules\feedback\domain\entity\FeedbackIdea;

interface IFeedbackIdeaRepository
{
    public function save(FeedbackIdea $idea): FeedbackIdea;
    public function findById(int $id): ?FeedbackIdea;
    /** @return FeedbackIdea[] */
    public function findAll(array $filters = []): array;
    public function countAll(): int;
    public function countImplemented(): int;
}
