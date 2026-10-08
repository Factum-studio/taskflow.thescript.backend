<?php

declare(strict_types=1);

namespace core\application\handler;

use core\application\dto\UserDto;
use core\application\port\IUserRepository;
use core\application\query\ListUserQuery;

class ListUserHandler
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param ListUserQuery $query
     * @return UserDto[]
     */
    public function handle(ListUserQuery $query): array
    {
        $users = $this->userRepository->findWithFilters($query->filters);
        return array_map([UserDto::class, 'fromEntity'], $users);
    }
}
