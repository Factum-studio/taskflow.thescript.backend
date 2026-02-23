<?php

namespace core\application\useCase;

use core\application\port\IJwtValidator;
use core\domain\valueObject\JwtToken;
use core\domain\valueObject\Identity;
use core\domain\exception\InvalidJwtException;

final class AuthenticateByJwtUseCase
{
    private IJwtValidator $jwtValidator;

    public function __construct(IJwtValidator $jwtValidator)
    {
        $this->jwtValidator = $jwtValidator;
    }

    /**
     * @throws InvalidJwtException
     */
    public function execute(JwtToken $token): Identity
    {
        $payload = $this->jwtValidator->validate($token);

        return Identity::fromString($payload->subject);
    }
}