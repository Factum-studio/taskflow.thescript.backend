<?php

declare(strict_types=1);

namespace modules\passport\auth\infrastructure\http;

use modules\passport\auth\application\dto\PassportTokenDto;
use modules\passport\auth\application\dto\PassportUserDto;
use modules\passport\auth\application\port\PassportAuthPort;
use RuntimeException;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\httpclient\Response;

final class PassportHttpClient implements PassportAuthPort
{
    /**
     * @param string[] $scopes
     */
    public function __construct(
        private readonly Client $client,
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
        private readonly array $scopes,
        private readonly string $userEndpoint = '/users/{id}',
    ) {
    }

    public function buildAuthorizationUrl(string $state): string
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $this->scopes),
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        return rtrim($this->baseUrl, '/') . '/auth/authorize?' . $query;
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function exchangeAuthorizationCode(string $code): PassportTokenDto
    {
        $response = $this->client->createRequest()
            ->setMethod('POST')
            ->setUrl(rtrim($this->baseUrl, '/') . '/token')
            ->setFormat(Client::FORMAT_URLENCODED)
            ->setData([
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
            ])
            ->send();

        return $this->parseTokenResponse($response);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function refresh(string $refreshToken): PassportTokenDto
    {
        $response = $this->client->createRequest()
            ->setMethod('POST')
            ->setUrl(rtrim($this->baseUrl, '/') . '/token')
            ->setFormat(Client::FORMAT_URLENCODED)
            ->setData([
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
                'scope' => implode(' ', $this->scopes),
            ])
            ->send();

        return $this->parseTokenResponse($response);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getUser(string $passportId, string $accessToken): PassportUserDto
    {
        $url = str_replace('{id}', rawurlencode($passportId), $this->userEndpoint);

        $response = $this->client->createRequest()
            ->setMethod('GET')
            ->setUrl(rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/'))
            ->addHeaders(['Authorization' => 'Bearer ' . $accessToken])
            ->send();

        if (!$response->getIsOk()) {
            throw new RuntimeException('Passport user request failed: HTTP ' . $response->getStatusCode());
        }

        $body = $response->getData();
        if (!is_array($body)) {
            throw new RuntimeException('Invalid Passport user response');
        }

        $data = $body['item'] ?? $body['data'] ?? $body;
        if (isset($data['item']) && is_array($data['item'])) {
            $data = $data['item'];
        }
        if (!is_array($data)) {
            throw new RuntimeException('Invalid Passport user payload');
        }

        $id = $data['id'] ?? $data['sub'] ?? $passportId;
        if (!is_scalar($id)) {
            throw new RuntimeException('Invalid Passport user id');
        }

        // --- post ---
        $post = null;
        $postId = $data['post_id'] ?? null;
        if ($postId !== null && $postId !== '' && is_scalar($postId)) {
            $post = $this->fetchPostName((string)$postId, $accessToken);
        }
        // фолбэк, если API когда-нибудь начнёт отдавать post строкой
        if ($post === null && isset($data['post']) && is_scalar($data['post'])) {
            $post = (string)$data['post'];
        }

        // --- isOwner ---
        $isOwner = (bool)($data['isOwner'] ?? $data['is_owner'] ?? false);

        if (!$isOwner && isset($data['roles']) && is_array($data['roles'])) {
            $roleIds = [];
            foreach ($data['roles'] as $role) {
                if (!is_array($role)) {
                    if (is_scalar($role)) {
                        $roleIds[] = (string)$role;
                    }
                    continue;
                }
                // если это уже развёрнутая роль — проверим name сразу
                if (isset($role['name']) && $role['name'] === 'owner') {
                    $isOwner = true;
                    continue;
                }
                if (isset($role['role_id']) && is_scalar($role['role_id'])) {
                    $roleIds[] = (string)$role['role_id'];
                }
            }

            $roleIds = array_values(array_unique($roleIds));
            if (!$isOwner && $roleIds !== []) {
                $isOwner = $this->hasOwnerRole($roleIds, $accessToken);
            }
        }

        return new PassportUserDto(
            (string)$id,
            (string)($data['surname'] ?? ''),
            (string)($data['name'] ?? ''),
            isset($data['patronymic']) ? (string)$data['patronymic'] : null,
            (string)($data['email'] ?? ''),
            $post,
            $isOwner,
        );
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    private function fetchPostName(string $postId, string $accessToken): ?string
    {
        $url = rtrim($this->baseUrl, '/') . '/posts?' . http_build_query(['ids' => $postId]);

        $response = $this->client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->addHeaders(['Authorization' => 'Bearer ' . $accessToken])
            ->send();

        if (!$response->getIsOk()) {
            return null;
        }

        $body = $response->getData();
        if (!is_array($body)) {
            return null;
        }

        $items = $body['items'] ?? $body['item'] ?? $body['data'] ?? $body;
        if (isset($items['items']) && is_array($items['items'])) {
            $items = $items['items'];
        }
        if (!is_array($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (is_array($item) && isset($item['name']) && is_scalar($item['name'])) {
                return (string)$item['name'];
            }
        }

        return null;
    }

    /**
     * @param string[] $roleIds
     * @throws Exception
     * @throws InvalidConfigException
     */
    private function hasOwnerRole(array $roleIds, string $accessToken): bool
    {
        $url = rtrim($this->baseUrl, '/') . '/roles?' . http_build_query(['ids' => implode(',', $roleIds)]);

        $response = $this->client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->addHeaders(['Authorization' => 'Bearer ' . $accessToken])
            ->send();

        if (!$response->getIsOk()) {
            return false;
        }

        $body = $response->getData();
        if (!is_array($body)) {
            return false;
        }

        $items = $body['items'] ?? $body['item'] ?? $body['data'] ?? $body;
        if (isset($items['items']) && is_array($items['items'])) {
            $items = $items['items'];
        }
        if (!is_array($items)) {
            return false;
        }

        foreach ($items as $item) {
            if (is_array($item) && isset($item['name']) && $item['name'] === 'owner') {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function logout(?string $cookieHeader, ?string $authorization): array
    {
        $request = $this->client->createRequest()
            ->setMethod('GET')
            ->setUrl(rtrim($this->baseUrl, '/') . '/auth/logout');

        if ($cookieHeader !== null && $cookieHeader !== '') {
            $request->addHeaders(['Cookie' => $cookieHeader]);
        }
        if ($authorization !== null && trim($authorization) !== '') {
            $request->addHeaders(['Authorization' => $authorization]);
        }

        $response = $request->send();

        $headers = [];
        foreach ($response->getHeaders()->toArray() as $name => $values) {
            if (in_array(strtolower($name), ['set-cookie', 'location', 'content-type'], true)) {
                $headers[$name] = is_array($values) ? $values : [$values];
            }
        }

        return [
            'status' => $response->getStatusCode(),
            'headers' => $headers,
            'body' => $response->getContent(),
        ];
    }

    /**
     * @throws Exception
     */
    private function parseTokenResponse(Response $response): PassportTokenDto
    {
        if (!$response->getIsOk()) {
            throw new RuntimeException('Passport token request failed: HTTP ' . $response->getStatusCode());
        }

        $body = $response->getData();
        if (!is_array($body)) {
            throw new RuntimeException('Invalid Passport token response');
        }

        $data = $body['data'] ?? $body;
        if (!is_array($data)) {
            throw new RuntimeException('Invalid Passport token payload');
        }

        $accessToken = $data['access_token'] ?? null;
        if (!is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Passport did not return access_token');
        }

        $refreshToken = $data['refresh_token'] ?? null;
        if ($refreshToken !== null && !is_string($refreshToken)) {
            $refreshToken = null;
        }

        $scopes = $data['scope'] ?? $data['scopes'] ?? [];
        if (is_string($scopes)) {
            $scopes = preg_split('/\s+/', trim($scopes), -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!is_array($scopes)) {
            $scopes = [];
        }

        return new PassportTokenDto(
            $accessToken,
            $refreshToken,
            max(1, (int)($data['expires_in'] ?? 3600)),
            array_values(array_map('strval', $scopes)),
            (string)($data['token_type'] ?? 'Bearer'),
        );
    }
}
