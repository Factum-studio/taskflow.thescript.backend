<?php

declare(strict_types=1);

namespace modules\rbac\presentation\controller;

use core\application\dto\CollectionDto;
use core\presentation\controller\BaseController;
use modules\rbac\application\dto\PermissionDto;
use modules\rbac\application\port\IPermissionRepository;
use OpenApi\Attributes as OA;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;

#[OA\Tag(
    name: 'rbac',
    description: 'RBAC permissions',
)]
final class PermissionController extends BaseController
{
    #[OA\Get(
        path: '/rbac/permissions',
        summary: 'Список доступных permissions',
        security: [
            ['bearerAuth' => []],
            ['accessTokenCookie' => []],
        ],
        tags: ['rbac'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список permissions',
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
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function actionIndex(): CollectionDto
    {
        $items = array_map(
            static fn ($permission): PermissionDto => PermissionDto::fromDomain($permission),
            Yii::$container->get(IPermissionRepository::class)->findAll(),
        );

        return $this->collection($items);
    }
}
