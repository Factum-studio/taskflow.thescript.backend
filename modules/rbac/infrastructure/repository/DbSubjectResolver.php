<?php

declare(strict_types=1);

namespace modules\rbac\infrastructure\repository;

use modules\rbac\application\port\ISubjectResolver;
use yii\db\Query;

final class DbSubjectResolver implements ISubjectResolver
{
    /**
     * @param array<string, mixed> $subject
     */
    public function resolveOwner(array $subject): ?int
    {
        $resource = (string)($subject['resource'] ?? '');
        $id = isset($subject['id']) ? (int)$subject['id'] : 0;

        if ($id <= 0) {
            return null;
        }

        $query = match ($resource) {
            // @template 'storage_files' => (new Query())->from('storage_files')->select('user_id')->where(['id' => $id]),
            default => null,
        };

        if (!$query instanceof Query) {
            return null;
        }

        $ownerId = $query->scalar();

        return $ownerId === false || $ownerId === null ? null : (int)$ownerId;
    }
}
