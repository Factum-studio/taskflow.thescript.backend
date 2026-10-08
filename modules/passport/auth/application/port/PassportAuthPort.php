<?php

declare(strict_types=1);

namespace modules\passport\auth\application\port;

use modules\passport\auth\application\dto\PassportTokenDto;
use modules\passport\auth\application\dto\PassportUserDto;

interface PassportAuthPort
{
    public function buildAuthorizationUrl(string $state): string;

    public function exchangeAuthorizationCode(string $code): PassportTokenDto;

    public function refresh(string $refreshToken): PassportTokenDto;

    public function getUser(string $passportId, string $accessToken): PassportUserDto;

    /**
     * Returns the response headers/body needed to proxy Passport logout.
     *
     * @return array{status:int, headers:array<string, string[]>, body:string}
     */
    public function logout(?string $cookieHeader, ?string $authorization): array;
}
