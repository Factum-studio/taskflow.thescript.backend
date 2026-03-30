<?php

namespace modules\projects\infrastructure\listener;

use core\domain\event\UserFirstLoginEvent;
use modules\projects\application\command\CreateProjectCommand;
use modules\projects\application\handler\CreateProjectHandler;
use RuntimeException;
use Yii;

class CreatePersonalProjectOnUserFirstLogin
{
    private CreateProjectHandler $createProjectHandler;

    public function __construct(CreateProjectHandler $createProjectHandler)
    {
        $this->createProjectHandler = $createProjectHandler;
    }

    public function handle(UserFirstLoginEvent $event): void
    {
        $command = new CreateProjectCommand(
            name: 'Личный проект пользователя #'.$event->getUserId(),
            type: 'personal',
            ownerId: $event->getUserId(),
            settings: []
        );

        try {
            $this->createProjectHandler->handle($command);
            Yii::info("Personal project created for user {$event->getUserId()}", 'projects');
        } catch (RuntimeException $e) {
            Yii::error("Failed to create personal project for user {$event->getUserId()}: " . $e->getMessage(), 'projects');
        }
    }
}