<?php

declare(strict_types=1);

namespace modules\rbac\infrastructure\repository;

use modules\rbac\application\port\IPermissionRepository;
use modules\rbac\domain\valueObject\Permission;
use Yii;
use yii\db\Exception;
use yii\db\Query;

final class DbPermissionRepository implements IPermissionRepository
{
    public function findByCode(string $code): ?Permission
    {
        $row = (new Query())
            ->from('{{%permissions}}')
            ->select(['id', 'code'])
            ->where(['code' => $code])
            ->one();

        return $row === false ? null : new Permission(
            (int)$row['id'],
            (string)$row['code'],
        );
    }

    public function findCodesByUserId(int $userId): array
    {
        $rows = (new Query())
            ->select(['p.code'])
            ->from(['ur' => '{{%user_roles}}'])
            ->innerJoin(['rp' => '{{%role_permissions}}'], 'rp.role_id = ur.role_id')
            ->innerJoin(['p' => '{{%permissions}}'], 'p.id = rp.permission_id')
            ->where(['ur.user_id' => $userId])
            ->column();

        return array_values(array_unique(array_map('strval', $rows)));
    }

    public function findAll(): array
    {
        $rows = (new Query())
            ->from('{{%permissions}}')
            ->select(['id', 'code'])
            ->orderBy(['code' => SORT_ASC])
            ->all();

        return array_map(
            static fn (array $row): Permission => new Permission(
                (int)$row['id'],
                (string)$row['code'],
            ),
            $rows,
        );
    }

    public function findByRoleId(int $roleId): array
    {
        $rows = (new Query())
            ->select(['p.id', 'p.code'])
            ->from(['p' => '{{%permissions}}'])
            ->innerJoin(['rp' => '{{%role_permissions}}'], 'rp.permission_id = p.id')
            ->where(['rp.role_id' => $roleId])
            ->orderBy(['p.code' => SORT_ASC])
            ->all();

        return array_map(
            static fn (array $row): Permission => new Permission(
                (int)$row['id'],
                (string)$row['code'],
            ),
            $rows,
        );
    }

    /**
     * @throws Exception
     */
    public function grant(int $roleId, int $permissionId): void
    {
        Yii::$app->db->createCommand()
            ->upsert('{{%role_permissions}}', [
                'role_id'       => $roleId,
                'permission_id' => $permissionId,
            ], false)
            ->execute();
    }

    /**
     * @throws Exception
     */
    public function revoke(int $roleId, int $permissionId): void
    {
        Yii::$app->db->createCommand()->delete('{{%role_permissions}}', [
            'role_id'       => $roleId,
            'permission_id' => $permissionId,
        ])->execute();
    }
}
