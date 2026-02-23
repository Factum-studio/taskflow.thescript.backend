<?php

namespace core\application\port;

use core\domain\valueObject\QueryParams;

interface IUrlBuilder
{
    public function build(string $endpoint, ?QueryParams $params = null): string;
    public function withBaseUrl(string $baseUrl): self;
    public function withVersion(string $version): self;
}