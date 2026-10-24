<?php

declare(strict_types=1);

namespace modules\projectRbac\config;

return [
    // ── Default tariff limits (before real billing is connected) ──
    //
    // These apply globally. When the billing module is implemented,
    // IBillingLimitsService will read per-subscription limits instead.

    // Quotas
    'max_projects' => 10,
    'max_members'  => 50,
    'max_boards'   => 10,
    'max_columns'  => 20,

    // Functional segmentation (tariff features).
    // null / empty array = no restriction (all features enabled).
    // Non-empty array = whitelist of permission codes available on this tier.
    // '*' = all features.
    'available_features' => ['*'],
];
