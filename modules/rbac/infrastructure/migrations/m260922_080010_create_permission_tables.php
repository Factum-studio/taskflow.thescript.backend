<?php

declare(strict_types=1);

namespace modules\rbac\infrastructure\migrations;

use yii\db\Migration;
use yii\db\Query;

class m260922_080010_create_permission_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('permissions', [
            'id'            => $this->bigPrimaryKey(),
            'code'          => $this->string(100)->notNull()->unique(),
            'description'   => $this->string(255)->null(),
            'created_at'    => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createTable('role_permissions', [
            'role_id'       => $this->bigInteger()->notNull(),
            'permission_id' => $this->bigInteger()->notNull(),
        ]);

        $this->addPrimaryKey(
            'pk_role_permissions',
            'role_permissions',
            ['role_id', 'permission_id'],
        );

        $this->addForeignKey(
            'fk_role_permissions_role_id',
            'role_permissions',
            'role_id',
            'roles',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $this->addForeignKey(
            'fk_role_permissions_permission_id',
            'role_permissions',
            'permission_id',
            'permissions',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $permissions = [
            // Users
            ['users.list',              'Просмотр списка локальных пользователей'],
            ['users.read.self',         'Просмотр собственного локального пользователя'],
            ['users.read.any',          'Просмотр любого локального пользователя'],

            // User roles
            ['user_roles.read',         'Просмотр назначенных ролей'],
            ['user_roles.manage',       'Назначение и снятие ролей'],

            // Roles
            ['roles.read',              'Просмотр ролей'],
            ['roles.manage',            'Создание, изменение и удаление ролей'],

            // System
            ['system.manage',           'Управление системными справочниками'],
            ['rbac.permissions.manage', 'Управление permissions ролей'],
        ];

        $this->batchInsert(
            'permissions',
            ['code', 'description'],
            $permissions,
        );

        $this->seedRolePermissions();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('role_permissions');
        $this->dropTable('permissions');
    }

    private function seedRolePermissions(): void
    {
        $roles = (new Query())
            ->select(['id', 'name'])
            ->from('roles')
            ->where(['name' => ['user', 'admin']])
            ->indexBy('name')
            ->all();

        $permissions = (new Query())
            ->select(['id', 'code'])
            ->from('permissions')
            ->indexBy('code')
            ->all();

        $mapping = [
            'user' => [
                'users.read.self',
                'user_roles.read',
            ],
            'admin' => [
                'users.list',
                'users.read.any',
                'user_roles.manage',
                'roles.read',
                'roles.manage',
                'system.manage',
                'rbac.permissions.manage',
            ],
        ];

        $rows = [];
        foreach ($mapping as $roleName => $permissionCodes) {
            if (!isset($roles[$roleName])) {
                continue;
            }

            foreach ($permissionCodes as $permissionCode) {
                if (!isset($permissions[$permissionCode])) {
                    continue;
                }

                $rows[] = [(int)$roles[$roleName]['id'], (int)$permissions[$permissionCode]['id']];
            }
        }

        if ($rows !== []) {
            $this->batchInsert('role_permissions', ['role_id', 'permission_id'], $rows);
        }
    }
}
