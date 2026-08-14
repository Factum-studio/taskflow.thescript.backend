<?php

namespace core\application\useCase;

use core\application\port\IJwtValidator;
use core\domain\valueObject\JwtToken;
use core\domain\valueObject\Identify;
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
    public function execute(JwtToken $token): Identify
    {
        $payload = $this->jwtValidator->validate($token);

        return Identify::fromString($payload->subject);
    }
}