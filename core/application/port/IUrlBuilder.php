<?php

namespace core\application\port;

use core\domain\valueObject\QueryParams;

interface IUrlBuilder
{
    public function build(string $endpoint, ?QueryParams $params = null): string;
    public function buildWithId(string $endpoint, string|int $id, ?QueryParams $params = null): string;
    public function buildPath(string $path, ?QueryParams $params = null): string;
    public function withBaseUrl(string $baseUrl): self;
    public function withVersion(string $version): self;
    public function withDefaultParams(array $params): self;
}