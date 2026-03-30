<?php

namespace core\application\useCase;

use core\application\port\IEventDispatcher;
use core\application\port\ILocalUserRepository;
use core\application\port\IUserRepository;
use core\domain\entity\User;
use core\domain\event\UserFirstLoginEvent;
use core\domain\valueObject\JwtToken;
use core\domain\exception\InvalidJwtException;
use core\domain\exception\UserNotFoundException;

final class GetAuthenticatedUserUseCase
{
    public function __construct(
        private readonly AuthenticateByJwtUseCase $authenticateUseCase,
        private readonly IUserRepository $userRepository,
        private readonly ILocalUserRepository $localUserRepository,
        private readonly IEventDispatcher $eventDispatcher
    ) {}

    /**
     * @throws InvalidJwtException
     * @throws UserNotFoundException
     */
    public function execute(JwtToken $token): User
    {
        $userId = $this->authenticateUseCase->execute($token);
        $userIdValue = (int)$userId->value();

        $user = $this->userRepository->findById($userId, $token);

        if ($user === null) {
            throw new UserNotFoundException('User not found in Passport');
        }

        if (!$this->localUserRepository->exists($userIdValue)) {
            $email = $user->getContactByType('email')?->getValue() ?? '';
            $this->localUserRepository->create($userIdValue, $email);
            $this->eventDispatcher->dispatch(new UserFirstLoginEvent($userIdValue));
        }

        return $user;
    }
}