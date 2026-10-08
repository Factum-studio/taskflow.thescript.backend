<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\dto\UserDto;
use core\application\port\IUserRepository;
use core\application\query\GetUserQuery;
use core\domain\exception\UserNotFoundException;
use core\domain\exception\ValidationException;
use core\domain\valueObject\UserId;

class GetUserHandler
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     * @throws UserNotFoundException
     */
    public function handle(GetUserQuery $query): UserDto
    {
        $user = $this->userRepository->findById(new UserId($query->userId));
        return UserDto::fromEntity($user);
    }
}
