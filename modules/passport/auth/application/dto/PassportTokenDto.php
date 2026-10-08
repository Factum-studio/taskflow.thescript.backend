<?php

declare(strict_types=1);

namespace modules\passport\auth\application\dto;

final class PassportTokenDto
{
    /**
     * @param string[] $scopes
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken,
        public readonly int $expiresIn,
        public readonly array $scopes,
        public readonly string $tokenType = 'Bearer',
    ) {
    }
}
