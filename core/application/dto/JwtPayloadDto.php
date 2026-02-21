<?php

namespace core\application\dto;

final class JwtPayloadDto
{
    public function __construct(
        public string $subject,
        public string $issuer,
        public string $audience,
        public int $issuedAt,
        public int $expiresAt,
        public ?array $raw = null
    ) {}

    public function isExpired(int $now, int $clockSkew = 0): bool
    {
        return ($this->expiresAt + $clockSkew) < $now;
    }
}