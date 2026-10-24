<?php

declare(strict_types=1);

namespace modules\projectRbac\application\dto;

use JsonSerializable;

/**
 * DTO representing a single computed permission entry with source information.
 *
 * Used by the effective-permissions endpoint for the admin UI.
 */
class EffectivePermissionDto implements JsonSerializable
{
    /**
     * @param string $permissionCode e.g. "task.create"
     * @param bool $granted true = разрешено, false = запрещено
     * @param string $source "role" | "override" | "billing" | "business_rule"
     * @param string $sourceDetail Детали — e.g. "Admin (rank 100)" или "Тариф не позволяет"
     */
    public function __construct(
        public string $permissionCode,
        public bool $granted,
        public string $source,
        public string $sourceDetail = '',
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'permission_code' => $this->permissionCode,
            'granted'         => $this->granted,
            'source'          => $this->source,
            'source_detail'   => $this->sourceDetail,
        ];
    }
}
