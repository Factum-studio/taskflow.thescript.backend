<?php

declare(strict_types=1);

namespace modules\passport\auth\presentation\middleware;

use core\security\YiiIdentity;
use modules\passport\auth\application\port\PassportAuthPort;
use RuntimeException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\Cookie;
use yii\web\UnauthorizedHttpException;

final class PassportAuthMiddleware
{
    /**
     * @param array<int, string> $publicRoutes
     */
    public function __construct(
        private readonly PassportAuthPort $passport,
        private readonly array $publicRoutes,
        private readonly string $publicKeyPath,
        private readonly string $clientId,
        private readonly string $accessTokenCookieName = 'taskflow_access_token',
        private readonly string $refreshTokenCookieName = 'taskflow_refresh_token',
    ) {
    }

    /**
     * @throws InvalidConfigException
     * @throws UnauthorizedHttpException
     */
    public function handle(): void
    {
        $request = Yii::$app->request;
        $path = '/' . ltrim($request->getPathInfo(), '/');
        $method = strtoupper($request->getMethod());

        if ($method === 'OPTIONS' || $this->isPublic($method, $path)) {
            return;
        }

        $token = $this->extractAccessToken();
        if ($token === null) {
            $token = $this->tryRefresh();
        }

        if ($token === null) {
            $this->rejectUnauthorized('Authentication required');
        }

        try {
            $claims = $this->validateJwt($token);
        } catch (RuntimeException $e) {
            if ($e->getMessage() !== 'Access token expired') {
                $this->rejectUnauthorized($e->getMessage());
            }

            $token = $this->tryRefresh();
            if ($token === null) {
                $this->rejectUnauthorized('Authentication required');
            }

            try {
                $claims = $this->validateJwt($token);
            } catch (RuntimeException $refreshError) {
                $this->rejectUnauthorized($refreshError->getMessage());
            }
        }

        $sub = $claims['sub'] ?? null;
        $clientId = $claims['client_id'] ?? null;
        $tokenId = $claims['jti'] ?? null;
        $scopes = $claims['scopes'] ?? [];
        $audience = $claims['aud'] ?? null;

        if (
            (!is_string($sub) && !is_int($sub))
            || (!is_string($clientId) && $audience === null)
            || !is_string($tokenId)
            || !is_array($scopes)
        ) {
            $this->rejectUnauthorized('Invalid access token claims');
        }

        if ($clientId !== null && $clientId !== $this->clientId) {
            $this->rejectUnauthorized('Access token was issued for another client');
        }

        $audiences = is_array($audience) ? array_map('strval', $audience) : [(string)$audience];
        if (!in_array($this->clientId, $audiences, true)) {
            $this->rejectUnauthorized('Access token was issued for another audience');
        }

        Yii::$app->user->setIdentity(new YiiIdentity(
            (string)$sub,
            $clientId ?? (string)$audience,
            $tokenId,
            array_values(array_map('strval', $scopes)),
            'user',
        ));
    }

    private function extractAccessToken(): ?string
    {
        $authorization = Yii::$app->request->getHeaders()->get('Authorization');
        if (is_string($authorization) && preg_match('/^Bearer\s+(.+)$/i', trim($authorization), $matches) === 1) {
            return trim($matches[1]);
        }

        $cookieToken = Yii::$app->request->cookies->getValue($this->accessTokenCookieName);
        return is_string($cookieToken) && $cookieToken !== '' ? $cookieToken : null;
    }

    private function tryRefresh(): ?string
    {
        $refreshToken = Yii::$app->request->cookies->getValue($this->refreshTokenCookieName);
        if (!is_string($refreshToken) || $refreshToken === '') {
            return null;
        }

        try {
            $tokens = $this->passport->refresh($refreshToken);
            $this->setTokenCookies($tokens->accessToken, $tokens->refreshToken, $tokens->expiresIn);

            return $tokens->accessToken;
        } catch (Throwable) {
            $this->deleteTokenCookies();

            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validateJwt(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid access token');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $header = $this->decodeJson($encodedHeader);
        $payload = $this->decodeJson($encodedPayload);
        $signature = $this->base64UrlDecode($encodedSignature);

        if (($header['alg'] ?? null) !== 'RS256' || $signature === '') {
            throw new RuntimeException('Invalid access token signature');
        }

        if (!is_file($this->publicKeyPath) || !is_readable($this->publicKeyPath)) {
            throw new RuntimeException('OAuth public key is not readable');
        }

        $publicKey = openssl_pkey_get_public((string)file_get_contents($this->publicKeyPath));
        if ($publicKey === false) {
            throw new RuntimeException('Invalid OAuth public key');
        }

        $verified = openssl_verify(
            $encodedHeader . '.' . $encodedPayload,
            $signature,
            $publicKey,
            OPENSSL_ALGO_SHA256,
        );

        if ($verified !== 1) {
            throw new RuntimeException('Invalid access token signature');
        }

        $now = time();
        if (!isset($payload['exp']) || !is_numeric($payload['exp']) || (int)$payload['exp'] <= $now) {
            throw new RuntimeException('Access token expired');
        }

        if (isset($payload['nbf']) && is_numeric($payload['nbf']) && (int)$payload['nbf'] > $now) {
            throw new RuntimeException('Access token is not active yet');
        }

        $clientId = ($payload['client_id'] ?? null);

        if ($clientId !== null && $clientId !== $this->clientId) {
            throw new RuntimeException('Access token was issued for another client');
        }

        $audience = $payload['aud'] ?? null;
        $audiences = is_array($audience) ? array_map('strval', $audience) : [(string)$audience];
        if (!in_array($this->clientId, $audiences, true)) {
            throw new RuntimeException('Invalid access token audience');
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $encoded): array
    {
        $json = $this->base64UrlDecode($encoded);
        if ($json === '') {
            throw new RuntimeException('Invalid access token payload');
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid access token payload');
        }

        return $data;
    }

    private function base64UrlDecode(string $value): string
    {
        $value = strtr($value, '-_', '+/');
        $padding = strlen($value) % 4;
        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($value, true);

        return $decoded === false ? '' : $decoded;
    }

    private function isPublic(string $method, string $path): bool
    {
        foreach ($this->publicRoutes as $route) {
            [$publicMethod, $publicPath] = array_pad(explode(' ', trim($route), 2), 2, '');
            if (strtoupper($publicMethod) !== $method) {
                continue;
            }

            if ($publicPath === '*' || $publicPath === $path) {
                return true;
            }

            if (str_ends_with($publicPath, '*')
                && str_starts_with($path, rtrim($publicPath, '*'))
            ) {
                return true;
            }

            if (preg_match('/<[^>]+>/', $publicPath) === 1) {
                $pattern = '#^' . preg_replace('/<[^>]+>/', '[^/]+', $publicPath) . '$#';
                if (preg_match($pattern, $path) === 1) {
                    return true;
                }
            }
        }

        return false;
    }

    private function setTokenCookies(string $accessToken, ?string $refreshToken, int $expiresIn): void
    {
        Yii::$app->response->cookies->add(new Cookie([
            'name' => $this->accessTokenCookieName,
            'value' => $accessToken,
            'httpOnly' => true,
            'secure' => true,
            'sameSite' => Cookie::SAME_SITE_LAX,
            'path' => '/',
            'expire' => time() + $expiresIn,
        ]));

        if ($refreshToken !== null) {
            Yii::$app->response->cookies->add(new Cookie([
                'name' => $this->refreshTokenCookieName,
                'value' => $refreshToken,
                'httpOnly' => true,
                'secure' => true,
                'sameSite' => Cookie::SAME_SITE_LAX,
                'path' => '/',
                'expire' => time() + (int)($_ENV['OAUTH2_REFRESH_TOKEN_TTL'] ?? 2592000),
            ]));
        }
    }

    private function deleteTokenCookies(): void
    {
        foreach ([$this->accessTokenCookieName, $this->refreshTokenCookieName] as $name) {
            Yii::$app->response->cookies->remove(new Cookie([
                'name' => $name,
                'path' => '/',
            ]));
        }
    }

    /**
     * @throws UnauthorizedHttpException
     */
    private function rejectUnauthorized(string $message): never
    {
        Yii::$app->response->headers->set('WWW-Authenticate', 'Bearer realm="taskflow"');
        throw new UnauthorizedHttpException($message);
    }
}
