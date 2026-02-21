<?php

namespace core\application\port;

use core\application\dto\CollectionDto;
use core\domain\valueObject\JwtToken;

interface IPassportGateway
{
    public function getUserData(JwtToken $token): array;
}