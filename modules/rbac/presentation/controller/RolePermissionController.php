<?php

declare(strict_types=1);

namespace modules\rbac\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\SuccessDto;
use core\application\port\IRoleRepository;
use core\domain\exception\EntityNotFoundException;
use core\domain\valueObject\RoleId;
use core\presentation\controller\BaseController;
use modules\rbac\application\dto\PermissionDto;
use modules\rbac\application\port\IPermissionRepository;
use OpenApi\Attributes as OA;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Query;
use yii\di\NotInstantiableException;

#[OA\Tag(
    name: 'rbac',
    description: 'RBAC role permissions',
)]
final class RolePermissionController extends BaseController
{

    #[OA\Get(
        path: '/rbac/roles/{roleId}/permissions',
        summary: 'Permissions роли',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['rbac'],
        parameters: [
            new OA\Parameter(name: 'roleId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permissions роли',
                content: new OA\JsonContent(ref: '#/components/schemas/PermissionCollection'),
            ),
            new OA\Response(
                response: 403,
                description: 'Permission denied',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
            new OA\Response(
                response: 401,
                description: 'Authentication required',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
            new OA\Response(
                response: 404,
                description: 'Role not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws EntityNotFoundException
     */
    public function actionIndex(int $roleId): CollectionDto
    {
        $this->ensureRoleExists($roleId);
        $permissions = Yii::$container->get(IPermissionRepository::class)->findByRoleId($roleId);
        $items = array_map(
            static fn ($permission): PermissionDto => PermissionDto::fromDomain($permission),
            $permissions,
        );
        return $this->collection($items);
    }

    #[OA\Post(
        path: '/rbac/roles/{roleId}/permissions/{permissionId}',
        summary: 'Выдать permission роли',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['rbac'],
        parameters: [
            new OA\Parameter(name: 'roleId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'permissionId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permission выдан',
                content: new OA\JsonContent(ref: '#/components/schemas/Success'),
            ),
            new OA\Response(
                response: 403,
                description: 'Permission denied',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
            new OA\Response(
                response: 401,
                description: 'Authentication required',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
            new OA\Response(
                response: 404,
                description: 'Role or Permission not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws EntityNotFoundException
     */
    public function actionGrant(int $roleId, int $permissionId): SuccessDto
    {
        $this->ensureRoleExists($roleId);
        $permission = (new Query())
            ->from('{{%permissions}}')
            ->where(['id' => $permissionId])
            ->exists();

        if (!$permission) {
            throw new EntityNotFoundException('Permission not found.');
        }

        Yii::$container->get(IPermissionRepository::class)->grant($roleId, $permissionId);

        return $this->success(null, 'Permission granted');
    }

    #[OA\Delete(
        path: '/rbac/roles/{roleId}/permissions/{permissionId}',
        summary: 'Забрать permission у роли',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['rbac'],
        parameters: [
            new OA\Parameter(name: 'roleId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'permissionId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permission отозван',
                content: new OA\JsonContent(ref: '#/components/schemas/Success'),
            ),
            new OA\Response(
                response: 403,
                description: 'Permission denied',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
            new OA\Response(
                response: 401,
                description: 'Authentication required',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
            new OA\Response(
                response: 404,
                description: 'Role not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws EntityNotFoundException
     */
    public function actionRevoke(int $roleId, int $permissionId): SuccessDto
    {
        $this->ensureRoleExists($roleId);

        Yii::$container->get(IPermissionRepository::class)->revoke($roleId, $permissionId);

        return $this->success(null, 'Permission revoked');
    }

    /**
     * @throws EntityNotFoundException
     */
    private function ensureRoleExists(int $roleId): void
    {
        try {
            Yii::$container->get(IRoleRepository::class)->findById(
                new RoleId($roleId),
            );
        } catch (Throwable) {
            throw new EntityNotFoundException('Role not found.');
        }
    }
}
