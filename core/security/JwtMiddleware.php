<?php

namespace core\security;

use core\application\useCase\AuthenticateByJwtUseCase;
use core\domain\exception\InvalidJwtException;
use core\domain\valueObject\JwtToken;
use yii\web\UnauthorizedHttpException;
use core\security\YiiIdentity as YiiIdentity;

final class JwtMiddleware
{
    public function __construct(
        private AuthenticateByJwtUseCase $useCase
    ) {}

    public function handle(): void
    {
        $authHeader = \Yii::$app->request->headers->get('Authorization');

        if (!$authHeader) {
            throw new UnauthorizedHttpException('Authorization header missing');
        }

        if (!preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            throw new UnauthorizedHttpException('Invalid Authorization header format');
        }

        $tokenString = $matches[1] ?? null;

        if (!$tokenString) {
            throw new UnauthorizedHttpException('Empty bearer token');
        }

        try {
            $jwtToken = new JwtToken($tokenString);

            $domainIdentity = $this->useCase->execute($jwtToken);

            $yiiIdentity = new YiiIdentity($domainIdentity, $jwtToken);

            \Yii::$app->user->setIdentity($yiiIdentity);

        } catch (InvalidJwtException $e) {
            \Yii::warning(
                'JWT authentication failed: ' . $e->getMessage(),
                'security'
            );

            throw new UnauthorizedHttpException('Invalid credentials');
        }
    }
}