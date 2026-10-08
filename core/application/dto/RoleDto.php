<?php

declare(strict_types=1);

namespace core\application\dto;

use core\domain\entity\Role;
use JsonSerializable;

class RoleDto implements JsonSerializable
{
    private int $id;
    private string $name;
    private ?string $description;
    private string $createdAt;

    public function __construct(
        int $id,
        string $name,
        ?string $description,
        string $createdAt,
    ) {
        $this->id           = $id;
        $this->name         = $name;
        $this->description  = $description;
        $this->createdAt    = $createdAt;
    }

    public static function fromEntity(Role $role): self
    {
        return new self(
            $role->getId()->value(),
            $role->getName()->value(),
            $role->getDescription(),
            $role->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'created_at'    => $this->createdAt,
        ];
    }
}
