<?php

declare(strict_types=1);

namespace modules\feedback\application\handler;

use modules\feedback\application\command\SubmitIdeaCommand;
use modules\feedback\application\port\IFeedbackIdeaRepository;
use core\application\port\IEventDispatcher;
use modules\feedback\domain\entity\FeedbackIdea;
use modules\feedback\domain\event\IdeaSubmittedEvent;
use modules\feedback\domain\valueObject\IdeaType;

class SubmitIdeaHandler
{
    public function __construct(
        private IFeedbackIdeaRepository $ideaRepository,
        private IEventDispatcher $eventDispatcher,
    ) {
    }

    public function handle(SubmitIdeaCommand $command): FeedbackIdea
    {
        $type = new IdeaType($command->type);
        $idea = new FeedbackIdea(
            $command->userId,
            $type,
            $command->comment,
        );
        $idea = $this->ideaRepository->save($idea);

        $this->eventDispatcher->dispatch(new IdeaSubmittedEvent($idea));

        return $idea;
    }
}
