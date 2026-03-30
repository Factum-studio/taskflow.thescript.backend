<?php

namespace modules\projects\infrastructure\listener;

use modules\projects\domain\entity\ProjectUser;
use modules\projects\domain\event\ProjectCreatedEvent;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use modules\projects\domain\valueObject\UserRole;
use DateTimeImmutable;
use Yii;

class AddOwnerAsMemberListener
{
    private IProjectUserRepository $projectUserRepository;

    public function __construct(IProjectUserRepository $projectUserRepository)
    {
        $this->projectUserRepository = $projectUserRepository;
    }

    public function handleProjectCreated(ProjectCreatedEvent $event): void
    {
        try {
            $projectUser = new ProjectUser(
                new ProjectId($event->getAggregateId()),
                new UserId($event->getOwnerId()),
                new UserRole(UserRole::ADMIN),
                null,
                null,
                new DateTimeImmutable(),
                new DateTimeImmutable()
            );

            $this->projectUserRepository->save($projectUser);

            Yii::info(
                "User {$event->getOwnerId()} added as admin to project {$event->getAggregateId()}",
                'projects'
            );
        } catch (\Exception $e) {
            Yii::error(
                "Failed to add owner as project member: " . $e->getMessage(),
                'projects'
            );
        }
    }
}