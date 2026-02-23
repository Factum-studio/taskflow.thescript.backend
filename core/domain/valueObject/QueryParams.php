<?php

namespace core\domain\valueObject;

final class QueryParams
{
    private array $expand = [];
    private array $fields = [];
    private array $filters = [];
    private int $limit = 20;
    private int $page = 1;

    private function __construct() {}

    public static function fromArray(array $params): self
    {
        $instance = new self();

        if (isset($params['expand'])) {
            $instance->expand = $instance->normalizeArrayParam($params['expand']);
        }

        if (isset($params['fields'])) {
            $instance->fields = $instance->normalizeArrayParam($params['fields']);
        }

        if (isset($params['limit'])) {
            $instance->limit = (int)$params['limit'];
        }

        if (isset($params['page'])) {
            $instance->page = (int)$params['page'];
        }

        $instance->filters = array_diff_key(
            $params,
            array_flip(['expand', 'fields', 'limit', 'page'])
        );

        return $instance;
    }

    public static function fromQueryString(string $queryString): self
    {
        parse_str($queryString, $params);
        return self::fromArray($params);
    }

    public static function create(
        array|string $expand = [],
        array|string $fields = [],
        array $filters = [],
        int $limit = 20,
        int $page = 1
    ): self {
        $instance = new self();

        $instance->expand = $instance->normalizeArrayParam($expand);
        $instance->fields = $instance->normalizeArrayParam($fields);
        $instance->filters = $filters;
        $instance->limit = $limit;
        $instance->page = $page;

        return $instance;
    }

    private function normalizeArrayParam(array|string $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            return array_map('trim', explode(',', $value));
        }

        return [];
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function withLimit(int $limit): self
    {
        $clone = clone $this;
        $clone->limit = $limit;
        return $clone;
    }

    public function withPage(int $page): self
    {
        $clone = clone $this;
        $clone->page = $page;
        return $clone;
    }

    public function withExpand(array|string $expand): self
    {
        $clone = clone $this;
        $clone->expand = array_merge(
            $clone->expand,
            $this->normalizeArrayParam($expand)
        );
        return $clone;
    }

    public function withFields(array|string $fields): self
    {
        $clone = clone $this;
        $clone->fields = array_merge(
            $clone->fields,
            $this->normalizeArrayParam($fields)
        );
        return $clone;
    }

    public function withFilter(string $key, $value): self
    {
        $clone = clone $this;
        $clone->filters[$key] = $value;
        return $clone;
    }

    public function toArray(): array
    {
        $params = $this->filters;

        $params['page'] = $this->page;
        $params['limit'] = $this->limit;

        if (!empty($this->expand)) {
            $params['expand'] = implode(',', array_unique($this->expand));
        }

        if (!empty($this->fields)) {
            $params['fields'] = implode(',', array_unique($this->fields));
        }

        return $params;
    }

    public function getExpand(): array
    {
        return $this->expand;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}