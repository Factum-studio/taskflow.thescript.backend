<?php
namespace core\application\handler;

use core\application\command\SubmitIdeaCommand;
use core\application\port\IFeedbackIdeaRepository;
use core\application\port\IEventDispatcher;
use core\domain\entity\FeedbackIdea;
use core\domain\event\IdeaSubmittedEvent;
use core\domain\valueObject\IdeaType;

class SubmitIdeaHandler
{
    public function __construct(
        private IFeedbackIdeaRepository $ideaRepository,
        private IEventDispatcher $eventDispatcher
    ) {}

    public function handle(SubmitIdeaCommand $command): FeedbackIdea
    {
        $type = new IdeaType($command->type);
        $idea = new FeedbackIdea(
            $command->userId,
            $type,
            $command->comment
        );
        $idea = $this->ideaRepository->save($idea);

        $this->eventDispatcher->dispatch(new IdeaSubmittedEvent($idea));

        return $idea;
    }
}