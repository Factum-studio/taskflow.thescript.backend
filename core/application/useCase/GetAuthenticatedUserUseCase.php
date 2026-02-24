<?php

namespace core\application\useCase;

use core\application\port\IUserRepository;
use core\domain\entity\User;
use core\domain\valueObject\JwtToken;
use core\domain\exception\InvalidJwtException;
use core\domain\exception\UserNotFoundException;

final class GetAuthenticatedUserUseCase
{
    public function __construct(
        private readonly AuthenticateByJwtUseCase $authenticateUseCase,
        private readonly IUserRepository $userRepository
    ) {}

    /**
     * @throws InvalidJwtException
     * @throws UserNotFoundException
     */
    public function execute(JwtToken $token): User
    {
        $userId = $this->authenticateUseCase->execute($token);

        $user = $this->userRepository->findById($userId, $token);

        if ($user === null) {
            throw new UserNotFoundException('User not found in Passport');
        }

        return $user;
    }
}