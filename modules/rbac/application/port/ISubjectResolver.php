<?php

declare(strict_types=1);

namespace modules\rbac\application\port;

interface ISubjectResolver
{
    /**
     * @param array<string, mixed> $subject
     * @return int|null User ID которому принадлежит запрошенный ресурс.
     */
    public function resolveOwner(array $subject): ?int;
}
