<?php

declare(strict_types=1);

namespace modules\projects\infrastructure\listener;

use core\application\port\ITransactionManager;
use core\domain\valueObject\UserId;
use modules\projects\domain\entity\ProjectUser;
use modules\projects\domain\event\ProjectCreatedEvent;
use modules\projects\domain\repository\IProjectUserRepository;
use modules\projects\domain\repository\IProjectRepository;
use modules\projectRbac\application\port\ILocalRbacService;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserRole;
use DateTimeImmutable;
use RuntimeException;
use Throwable;
use Yii;

class AddOwnerAsMemberListener
{
    private IProjectUserRepository $projectUserRepository;
    private IProjectRepository $projectRepository;
    private ILocalRbacService $localRbac;
    private ITransactionManager $transactionManager;

    public function __construct(
        IProjectUserRepository $projectUserRepository,
        IProjectRepository $projectRepository,
        ILocalRbacService $localRbac,
        ITransactionManager $transactionManager,
    ) {
        $this->projectUserRepository = $projectUserRepository;
        $this->projectRepository = $projectRepository;
        $this->localRbac = $localRbac;
        $this->transactionManager = $transactionManager;
    }

    public function handleProjectCreated(ProjectCreatedEvent $event): void
    {
        try {
            $this->transactionManager->run(function () use ($event): void {
                $projectId = new ProjectId($event->getAggregateId());
                $projectUser = new ProjectUser(
                    $projectId,
                    new UserId($event->getOwnerId()),
                    new UserRole(UserRole::ADMIN),
                    null,
                    null,
                    new DateTimeImmutable(),
                    new DateTimeImmutable(),
                );

                $this->projectUserRepository->save($projectUser);
                $project = $this->projectRepository->findById($projectId);
                if ($project === null) {
                    throw new RuntimeException('Created project could not be reloaded for RBAC seeding');
                }
                $this->localRbac->seedProjectRoles($project);
            });

            Yii::info(
                "User {$event->getOwnerId()} added as admin to project {$event->getAggregateId()}",
                'projects',
            );
        } catch (Throwable $e) {
            Yii::error(
                "Failed to add owner as project member: " . $e->getMessage(),
                'projects',
            );
            throw $e;
        }
    }
}
