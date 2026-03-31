<?php

namespace modules\projects\infrastructure\listener;

use modules\projects\domain\event\BoardCreatedEvent;
use modules\projects\domain\event\ProjectCreatedEvent;
use modules\projects\domain\event\ProjectMemberAddedEvent;
use modules\projects\domain\event\ProjectMemberRemovedEvent;
use Yii;

class ProjectLoggerListener
{
    public function handleProjectCreated(ProjectCreatedEvent $event): void
    {
        Yii::info("Project created: ID {$event->getAggregateId()}, owner ID {$event->getOwnerId()}", 'projects');
    }

    public function handleProjectMemberAdded(ProjectMemberAddedEvent $event): void
    {
        Yii::info(
            "User {$event->getUserId()} added to project {$event->getAggregateId()} as {$event->getRole()} by user {$event->getAddedBy()}",
            'projects'
        );
    }

    public function handleProjectMemberRemoved(ProjectMemberRemovedEvent $event): void
    {
        Yii::info(
            "User {$event->getUserId()} removed from project {$event->getAggregateId()} by user {$event->getRemovedBy()}",
            'projects'
        );
    }

    public function handleBoardCreated(BoardCreatedEvent $event): void
    {
        Yii::info(
            "Board created: ID {$event->getAggregateId()} in project {$event->getProjectId()} by user {$event->getCreatedBy()}",
            'projects'
        );
    }
}