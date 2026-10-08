<?php

declare(strict_types=1);

namespace modules\rbac\application\dto;

use modules\rbac\domain\valueObject\Permission;
use JsonSerializable;

final class PermissionDto implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
    ) {
    }

    public static function fromDomain(Permission $permission): self
    {
        return new self(
            $permission->id(),
            $permission->code(),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id'    => $this->id,
            'code'  => $this->code,
        ];
    }
}
