<?php

namespace core\infrastructure\http\config;

final class ApiEndpointConfig
{
    private array $endpoints;

    public function __construct(array $endpoints = [])
    {
        $this->endpoints = $endpoints;
    }

    public function get(string $key): string
    {
        if (!isset($this->endpoints[$key])) {
            throw new \InvalidArgumentException("Endpoint '{$key}' not found");
        }

        return $this->endpoints[$key];
    }

    public function withEndpoint(string $key, string $path): self
    {
        $clone = clone $this;
        $clone->endpoints[$key] = $path;
        return $clone;
    }
}