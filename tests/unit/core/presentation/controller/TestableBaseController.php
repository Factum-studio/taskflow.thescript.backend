<?php

namespace tests\unit\core\presentation\controller;

use core\presentation\controller\BaseController;

class TestableBaseController extends BaseController
{
    /**
     * @var mixed Заглушка для user->id
     */
    private $stubUserId = null;

    /**
     * @var array Заглушка для request->get()
     */
    private $stubRequestParams = [];

    public function publicItem($dto)
    {
        return $this->item($dto);
    }

    public function publicCollection(array $items, ?int $total = null, ?int $page = null, ?int $limit = null)
    {
        return $this->collection($items, $total, $page, $limit);
    }

    public function publicError(string $message, int $code = 400, array $details = [])
    {
        return $this->error($message, $code, $details);
    }

    public function publicSuccess($data = null, string $message = 'OK')
    {
        return $this->success($data, $message);
    }

    public function publicParseIdRangeFromPath(?string $param)
    {
        return $this->parseIdRangeFromPath($param);
    }

    /* --------------/ Полное переопределение базовых методов /-------------- */
    public function getUserId(): ?int
    {
        return $this->stubUserId;
    }

    public function getLimit(): int
    {
        $limit = $this->stubRequestParams['limit'] ?? $this->defaultPageSize;
        return max($this->pageSizeLimit[0], min((int)$limit, $this->pageSizeLimit[1]));
    }

    public function getPage(): int
    {
        $page = $this->stubRequestParams['page'] ?? 1;
        return max(1, (int)$page);
    }
    /* --------------/ Полное переопределение базовых методов /-------------- */

    public function publicGetUserId(): ?int
    {
        return $this->getUserId();
    }

    public function publicGetLimit(): int
    {
        return $this->getLimit();
    }

    public function publicGetPage(): int
    {
        return $this->getPage();
    }

    /**
     * Методы для управления заглушками в тестах
     */
    public function setStubUserId($userId): void
    {
        $this->stubUserId = $userId;
    }

    public function setStubRequestParam(string $key, $value): void
    {
        $this->stubRequestParams[$key] = $value;
    }

    public function setStubRequestParams(array $params): void
    {
        $this->stubRequestParams = $params;
    }

    public function resetStubs(): void
    {
        $this->stubUserId = null;
        $this->stubRequestParams = [];
    }
}