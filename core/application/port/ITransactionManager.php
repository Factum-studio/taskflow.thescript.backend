<?php

declare(strict_types=1);

namespace core\application\port;

interface ITransactionManager
{
    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public function run(callable $operation): mixed;
}
