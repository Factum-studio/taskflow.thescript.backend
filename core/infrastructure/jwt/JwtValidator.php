<?php

namespace core\infrastructure\jwt;

use core\application\dto\JwtPayloadDto;
use core\application\port\IJwtValidator;
use core\domain\valueObject\JwtToken;
use core\domain\exception\InvalidJwtException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


final class JwtValidator implements IJwtValidator
{
    public function __construct(
        private string $secret,
        private string $issuer,
        private string $audience,
        private int $clockSkew = 60
    ) {}

    public function validate(JwtToken $token): JwtPayloadDto
    {
        try {
            $decoded = JWT::decode(
                $token->value(),
                new Key($this->secret, 'HS256')
            );

            $payload = (array)$decoded;

            $now = time();

            if (($payload['iss'] ?? null) !== $this->issuer) {
                throw new InvalidJwtException('Invalid issuer');
            }

            if (($payload['aud'] ?? null) !== $this->audience) {
                throw new InvalidJwtException('Invalid audience');
            }

            if (!isset($payload['exp'])) {
                throw new InvalidJwtException('Token does not contain exp');
            }

            if (($payload['exp'] + $this->clockSkew) < $now) {
                throw new InvalidJwtException('Token expired');
            }

            //т.к. sub не передаётся passport, разрешаем user_id
            $subject = $payload['sub'] ?? $payload['user_id'] ?? null;
            if (!$subject) {
                throw new InvalidJwtException('Token does not contain subject');
            }

            return new JwtPayloadDto(
                subject: (string)$subject,
                issuer: (string)$payload['iss'],
                audience: (string)$payload['aud'],
                issuedAt: (int)($payload['iat'] ?? 0),
                expiresAt: (int)$payload['exp'],
                raw: $payload
            );

        } catch (\Throwable $e) {
            \Yii::warning('JWT validation failed: ' . $e->getMessage(), 'security');
            throw new InvalidJwtException('Invalid JWT token');
        }
    }
}