<?php

namespace core\infrastructure\http;

use core\application\port\IUrlBuilder;
use core\domain\valueObject\QueryParams;

final class ApiUrlBuilder implements IUrlBuilder
{
    private string $baseUrl;
    private string $version = 'v1';
    private array $defaultParams = [];

    public function __construct(string $baseUrl = '')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function build(
        string $endpoint,
        ?QueryParams $params = null
    ): string {
        $endpoint = ltrim($endpoint, '/');
        $url = "{$this->baseUrl}/{$this->version}/{$endpoint}";

        $allParams = $this->defaultParams;

        if ($params !== null) {
            $allParams = array_merge($allParams, $params->toArray());
        }

        if (!empty($allParams)) {
            $url .= '?' . http_build_query($allParams);
        }

        return $url;
    }

    /**
     * Построение URL для ресурса с ID в пути
     *
     * @param string $endpoint Базовый эндпоинт (например, 'user')
     * @param string|int $id ID ресурса
     * @param QueryParams|null $params Дополнительные query-параметры
     */
    public function buildWithId(string $endpoint, string|int $id, ?QueryParams $params = null): string
    {
        $fullEndpoint = $endpoint . '/' . $id;
        return $this->build($fullEndpoint, $params);
    }

    /**
     * Построение URL с кастомным путем (для сложных случаев)
     *
     * @param string $path Полный путь после версии (например, 'user/123/contacts')
     * @param QueryParams|null $params Query-параметры
     */
    public function buildPath(string $path, ?QueryParams $params = null): string
    {
        return $this->build($path, $params);
    }

    public function withBaseUrl(string $baseUrl): self
    {
        $clone = clone $this;
        $clone->baseUrl = rtrim($baseUrl, '/');
        return $clone;
    }

    public function withVersion(string $version): self
    {
        $clone = clone $this;
        $clone->version = $version;
        return $clone;
    }

    public function withDefaultParams(array $params): self
    {
        $clone = clone $this;
        $clone->defaultParams = $params;
        return $clone;
    }
}