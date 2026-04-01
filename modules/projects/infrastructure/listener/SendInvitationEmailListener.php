<?php

namespace modules\projects\infrastructure\listener;

use core\application\notification\Notification;
use core\application\notification\NotificationHub;
use core\application\port\IPassportGateway;
use modules\projects\domain\event\InvitationCreatedEvent;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\ProjectId;

class SendInvitationEmailListener
{
    private NotificationHub $notificationHub;
    private IProjectRepository $projectRepository;
    private IPassportGateway $passportGateway;

    public function __construct(
        NotificationHub $notificationHub,
        IProjectRepository $projectRepository,
        IPassportGateway $passportGateway
    ) {
        $this->notificationHub      = $notificationHub;
        $this->projectRepository    = $projectRepository;
        $this->passportGateway      = $passportGateway;
    }

    public function handle(InvitationCreatedEvent $event): void
    {
        $frontendUrl = $_ENV['FRONTEND_URL'];

        $project = $this->projectRepository->findById(new ProjectId($event->getProjectId()));
        $projectName = $project ? $project->getName() : 'Project';

        $inviterData = $this->passportGateway->getUserById((string)$event->getInvitedBy(), \Yii::$app->user->identity->getJwtToken());
        $inviterName = $inviterData['ui_name'] ?? '';
        $inviterAvatar = $inviterData['avatar'] ?? '';

        $params = [
            't' => $event->getToken(),
            'pid' => $event->getProjectId(),
            'pname' => $projectName,
            'iby' => json_encode([
                'name' => $inviterName,
                'avatar' => $inviterAvatar,
            ]),
            'email' => $event->getEmail(),
        ];
        $acceptUrl = rtrim($frontendUrl, '/') . '/accept?' . http_build_query($params);

        $subject = 'Invitation to project';
        $body = "You have been invited to join the project. Click here to accept: {$acceptUrl}";

        $notification = new Notification(
            'email',
            $event->getEmail(),
            $subject,
            $body,
            ['invitationId' => $event->getAggregateId()]
        );
        $this->notificationHub->send($notification);
    }
}