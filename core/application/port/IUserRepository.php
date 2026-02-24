<?php

namespace core\application\port;

use Core\Domain\Entity\User;
use Core\Domain\ValueObject\Identify;
use Core\Domain\ValueObject\JwtToken;

interface IUserRepository
{
    public function findById(Identify $id, JwtToken $token): ?User;
}