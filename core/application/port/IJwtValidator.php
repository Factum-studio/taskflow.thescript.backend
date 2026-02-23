<?php

namespace core\application\port;

use core\application\dto\JwtPayloadDto;
use core\domain\valueObject\JwtToken;

interface IJwtValidator
{
    /**
     * @throws \core\domain\exception\InvalidJwtException
     */
    public function validate(JwtToken $token): JwtPayloadDto;
}