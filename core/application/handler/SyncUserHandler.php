<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\command\SyncUserCommand;
use core\application\dto\UserDto;
use core\application\port\IEventDispatcher;
use core\application\port\IRoleRepository;
use core\application\port\ITransactionManager;
use core\application\port\IUserRepository;
use core\application\port\IUserRoleRepository;
use core\domain\entity\User;
use core\domain\entity\UserRole;
use core\domain\event\UserFirstLoginEvent;
use core\domain\exception\ValidationException;
use core\domain\valueObject\Email;
use core\domain\valueObject\RoleName;
use core\domain\valueObject\UserId;
use core\domain\valueObject\UserRoleId;
use DateTimeImmutable;
use Throwable;

class SyncUserHandler
{
    public function __construct(
        private readonly IUserRepository $userRepository,
        private readonly IRoleRepository $roleRepository,
        private readonly IUserRoleRepository $userRoleRepository,
        private readonly ITransactionManager $transactionManager,
        private readonly IEventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function handle(SyncUserCommand $command): UserDto
    {
        $user = new User(
            new UserId(0),
            $command->passportId,
            new Email($command->email),
            $command->surname,
            $command->name,
            $command->patronymic,
            $command->post,
            $command->isOwner,
        );

        $this->transactionManager->run(function () use ($user): void {
            $userExists = $this->userRepository->existsByEmail($user->getEmail());
            $this->userRepository->save($user);

            $defaultRole = $this->roleRepository->findByName(new RoleName('user'));
            $userRole = new UserRole(
                new UserRoleId(0),
                $user->getId(),
                $defaultRole->getId(),
                new DateTimeImmutable(),
                null,
            );

            $this->userRoleRepository->sync($userRole);
            $user->assignRole($userRole);

            if ($user->isOwner()) {
                $defaultRole = $this->roleRepository->findByName(new RoleName('admin'));
                $userRole = new UserRole(
                    new UserRoleId(0),
                    $user->getId(),
                    $defaultRole->getId(),
                    new DateTimeImmutable(),
                    null,
                );

                $this->userRoleRepository->sync($userRole);
                $user->assignRole($userRole);
            }

            if (!$userExists) {
                $this->eventDispatcher->dispatch(new UserFirstLoginEvent($user->getId()->value()));
            }
        });

        return UserDto::fromEntity($user);
    }
}
