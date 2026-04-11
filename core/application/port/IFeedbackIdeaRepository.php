<?php
namespace core\application\port;

use core\domain\entity\FeedbackIdea;

interface IFeedbackIdeaRepository
{
    public function save(FeedbackIdea $idea): FeedbackIdea;
    public function findById(int $id): ?FeedbackIdea;
    /** @return FeedbackIdea[] */
    public function findAll(array $filters = []): array;
    public function countAll(): int;
    public function countImplemented(): int;
}