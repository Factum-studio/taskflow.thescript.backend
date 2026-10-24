<?php

declare(strict_types=1);

namespace modules\projectRbac\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\domain\exception\ValidationException;
use core\presentation\controller\BaseController;
use modules\projectRbac\application\dto\EffectivePermissionDto;
use modules\projectRbac\application\port\ILocalRbacService;
use modules\projectRbac\application\port\IProjectRoleRepository;
use modules\projectRbac\application\port\IBillingLimitsService;
use modules\projectRbac\application\service\ProjectRoleService;
use modules\projectRbac\application\service\ProjectMembershipService;
use modules\projectRbac\domain\valueObject\ProjectRole as ProjectRoleVO;
use modules\projectRbac\presentation\request\CreateRoleRequest;
use modules\projectRbac\presentation\request\UpdateRoleRequest;
use modules\projectRbac\presentation\request\SetRolePermissionsRequest;
use modules\projectRbac\presentation\request\SetUserPermissionRequest;
use modules\projectRbac\presentation\request\TransferOwnershipRequest;
use modules\projects\domain\entity\Project;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\infrastructure\persistence\ProjectUserAR;
use RuntimeException;
use Yii;
use yii\web\NotFoundHttpException;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'project-rbac',
    description: 'Настройка локального RBAC проекта: роли, разрешения, computed matrix, выход, передача владения',
)]
class ProjectRbacController extends BaseController
{
    private ILocalRbacService $rbac;
    private IProjectRoleRepository $roleRepository;
    private IProjectRepository $projectRepository;
    private ProjectRoleService $roleService;
    private ProjectMembershipService $membershipService;
    private IBillingLimitsService $billingLimits;

    public function __construct(
        $id,
        $module,
        private readonly ILocalRbacService $rbacService,
        private readonly IProjectRoleRepository $roleRepo,
        private readonly IProjectRepository $projectRepo,
        private readonly ProjectRoleService $projectRoleService,
        private readonly ProjectMembershipService $membershipSvc,
        private readonly IBillingLimitsService $billingSvc,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
        $this->rbac                 = $rbacService;
        $this->roleRepository       = $roleRepo;
        $this->projectRepository    = $projectRepo;
        $this->roleService          = $projectRoleService;
        $this->membershipService    = $membershipSvc;
        $this->billingLimits        = $billingSvc;
    }

    // ═══════════════════════════════════════════════════════════
    //  Roles CRUD
    // ═══════════════════════════════════════════════════════════

    #[OA\Get(
        path: '/project/{projectId}/role',
        description: 'Возвращает список ролей проекта',
        summary: 'Список ролей проекта',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', description: 'ID проекта', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProjectRole')),
                        new OA\Property(property: '_meta', ref: '#/components/schemas/Collection/properties/_meta'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
        ],
    )]
    /**
     * GET /project/{projectId}/role
     */
    public function actionIndex(int $projectId): CollectionDto|ErrorDto
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        if (!$this->rbac->hasPermission((int)$userId, $projectId, 'project.view')) {
            return $this->error('Not allowed to view project roles', 403);
        }

        $roles = $this->roleRepository->findRolesByProject($projectId);
        $items = array_map([$this, 'roleToArray'], $roles);
        return $this->collection($items);
    }

    #[OA\Post(
        path: '/project/{projectId}/role',
        summary: 'Создать новую роль в проекте (только admin)',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateRoleRequest')),
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', description: 'ID проекта', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Роль создана'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Недостаточно прав'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
    /**
     * POST /project/{projectId}/role
     */
    public function actionCreate(int $projectId): ItemDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $request = new CreateRoleRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        if (!$this->rbac->hasPermission((int)$userId, $projectId, 'role.create')) {
            return $this->error('Role creation is not available for this project', 403);
        }
        if (!$this->roleService->canCreateRole((int)$userId, $projectId, (int)$request->rank)) {
            return $this->error('A role can only be created below your own rank', 403);
        }

        // Generate slug from name
        $nameForSlug = trim((string)$request->name);
        if (function_exists('iconv')) {
            $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nameForSlug);
            if ($transliterated !== false) {
                $nameForSlug = $transliterated;
            }
        }
        $slug = trim((string)preg_replace('/[^a-z0-9]+/', '-', strtolower($nameForSlug)), '-');
        if ($slug === '') {
            return $this->error('Role name must contain letters or digits', 422);
        }
        if (in_array($slug, ['admin', 'member', 'manager'], true)) {
            return $this->error('This role slug is reserved for a system role', 422);
        }

        try {
            $insertedId = $this->roleRepository->insertRole(
                $projectId,
                (string)$request->name,
                $slug,
                (int)$request->rank,
                false,
                false,
                (int)$userId,
            );
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 403);
        }

        return $this->item(['id' => $insertedId, 'name' => $request->name, 'rank' => $request->rank, 'slug' => $slug]);
    }

    #[OA\Put(
        path: '/project/{projectId}/role/{roleId}',
        summary: 'Обновить роль',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateRoleRequest')),
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', description: 'ID проекта', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'roleId', description: 'ID роли', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Роль обновлена'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Недостаточно прав'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
    /**
     * PUT /project/{projectId}/role/{roleId}
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $projectId, int $roleId): ErrorDto|ItemDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $role = $this->roleRepository->findRoleByIdInProject($projectId, $roleId);
        if (!$role) {
            throw new NotFoundHttpException('Role not found in this project');
        }
        if ($role->isSystem()) {
            return $this->error('System roles cannot be edited', 403);
        }

        if (!$this->rbac->hasPermission((int)$userId, $projectId, 'role.update')) {
            return $this->error('Role updates are not available for this project', 403);
        }

        $request = new UpdateRoleRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }
        if (!$this->roleService->canUpdateRole((int)$userId, $projectId, $role, (int)$request->rank)) {
            return $this->error('You can only edit roles ranked below you and cannot raise a role to your rank or higher', 403);
        }

        try {
            $this->roleRepository->updateRole($roleId, (string)$request->name, (int)$request->rank, (int)$userId);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
        return $this->item(['id' => $roleId, 'project_id' => $projectId, 'name' => $request->name, 'rank' => $request->rank]);
    }

    #[OA\Delete(
        path: '/project/{projectId}/role/{roleId}',
        summary: 'Удалить роль (кроме системных)',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', description: 'ID проекта', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'roleId', description: 'ID роли', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Роль удалена'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Недостаточно прав'),
            new OA\Response(response: 404, description: 'Роль не найдена'),
        ],
    )]
    /**
     * DELETE /project/{projectId}/role/{roleId}
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $projectId, int $roleId): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $role = $this->roleRepository->findRoleByIdInProject($projectId, $roleId);
        if (!$role) {
            throw new NotFoundHttpException('Role not found');
        }

        if (!$this->rbac->hasPermission((int)$userId, $projectId, 'role.delete')) {
            return $this->error('Role deletion is not available for this project', 403);
        }
        if (!$this->roleService->canDeleteRole((int)$userId, $projectId, $role)) {
            return $this->error('Cannot delete this role', 403);
        }

        try {
            $this->roleRepository->deleteRole($roleId);
            return $this->success(null, 'Role deleted');
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 403);
        }
    }

    // ═══════════════════════════════════════════════════════════
    //  Role Permissions
    // ═══════════════════════════════════════════════════════════

    #[OA\Get(
        path: '/project/{projectId}/role/{roleId}/permission',
        summary: 'Список разрешений роли',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'roleId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешный ответ'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Роль не найдена'),
        ],
    )]
    /**
     * GET /project/{projectId}/role/{roleId}/permission
     */
    public function actionRolePermissions(int $projectId, int $roleId): CollectionDto|ErrorDto
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $role = $this->roleRepository->findRoleByIdInProject($projectId, $roleId);
        if (!$role) {
            throw new NotFoundHttpException('Role not found in this project');
        }
        if (!$this->rbac->hasPermission((int)$userId, $projectId, 'project.view')) {
            return $this->error('Not allowed to view role permissions', 403);
        }
        $permissions = $this->rbac->getRolePermissions($roleId);
        $items = array_map(fn ($code) => ['permission_code' => $code], $permissions);
        return $this->collection($items);
    }

    #[OA\Put(
        path: '/project/{projectId}/role/{roleId}/permission',
        summary: 'Установить разрешения роли (заменяет все)',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/SetRolePermissionsRequest')),
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'roleId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Разрешения обновлены'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Недостаточно прав'),
        ],
    )]
    /**
     * PUT /project/{projectId}/role/{roleId}/permission
     */
    public function actionUpdateRolePermissions(int $projectId, int $roleId): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $role = $this->roleRepository->findRoleByIdInProject($projectId, $roleId);
        if (!$role) {
            throw new NotFoundHttpException('Role not found');
        }

        if (!$this->rbac->hasPermission((int)$userId, $projectId, 'role.permissions.manage')) {
            return $this->error('Role permission management is not available for this project', 403);
        }
        if (!$this->roleService->canManageRolePermissions((int)$userId, $projectId, $role)) {
            return $this->error('Cannot manage permissions for this role based on role hierarchy', 403);
        }

        $request = new SetRolePermissionsRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        foreach ((array)$request->permissions as $permissionCode) {
            if (!in_array($permissionCode, $this->roleRepository->findAllPermissionCodes(), true)) {
                return $this->error('Unknown permission code', 422, ['permissions' => ['Unknown permission code']]);
            }
        }
        $this->roleRepository->setRolePermissions($roleId, (array)$request->permissions, (int)$userId);
        return $this->success(null, 'Permissions updated');
    }

    // ═══════════════════════════════════════════════════════════
    //  User permission overrides
    // ═══════════════════════════════════════════════════════════

    #[OA\Get(
        path: '/project/{projectId}/member/{memberId}/permission',
        summary: 'Список индивидуальных override-разрешений участника',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'memberId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешный ответ'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
        ],
    )]
    public function actionMemberPermissions(int $projectId, int $memberId): CollectionDto|ErrorDto
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        if (!ProjectUserAR::findOne(['project_id' => $projectId, 'user_id' => $memberId, 'is_left' => 0])) {
            throw new NotFoundHttpException('Project member not found');
        }
        if (!$this->rbac->hasPermission((int)$userId, $projectId, 'member.permission.override')) {
            return $this->error('Not allowed to view permission overrides', 403);
        }
        if (!$this->roleService->canManageUserPermissions((int)$userId, $projectId, $memberId)) {
            return $this->error('You can only manage permission overrides for members ranked below you', 403);
        }
        $overrides = $this->roleRepository->findUserOverrides($projectId, $memberId);
        return $this->collection($overrides);
    }

    #[OA\Put(
        path: '/project/{projectId}/member/{memberId}/permission',
        summary: 'Установить или обновить индивидуальное разрешение участника',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/SetUserPermissionRequest')),
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'memberId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Override установлен'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Недостаточно прав'),
        ],
    )]
    public function actionUpdateMemberPermission(int $projectId, int $memberId): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        if (!ProjectUserAR::findOne(['project_id' => $projectId, 'user_id' => $memberId, 'is_left' => 0])) {
            throw new NotFoundHttpException('Project member not found');
        }

        // Only users with member.permission.override can manage overrides
        if (!$this->rbac->hasPermission((int)$userId, $projectId, 'member.permission.override')) {
            return $this->error('Not allowed to manage permission overrides', 403);
        }
        if (!$this->roleService->canManageUserPermissions((int)$userId, $projectId, $memberId)) {
            return $this->error('You can only manage permission overrides for members ranked below you', 403);
        }

        $request = new SetUserPermissionRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        if (!in_array((string)$request->permission_code, $this->roleRepository->findAllPermissionCodes(), true)) {
            return $this->error('Unknown permission code', 422, ['permission_code' => ['Unknown permission code']]);
        }

        $this->roleRepository->setUserOverride(
            $projectId,
            $memberId,
            (string)$request->permission_code,
            (bool)$request->is_granted,
            (int)$userId,
        );

        return $this->success(null, 'Override set');
    }

    #[OA\Delete(
        path: '/project/{projectId}/member/{memberId}/permission/{permissionCode}',
        summary: 'Удалить индивидуальный override разрешения',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'memberId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'permissionCode', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Override удалён'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Недостаточно прав'),
        ],
    )]
    public function actionDeleteMemberPermission(int $projectId, int $memberId, string $permissionCode): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        if (!ProjectUserAR::findOne(['project_id' => $projectId, 'user_id' => $memberId, 'is_left' => 0])) {
            throw new NotFoundHttpException('Project member not found');
        }
        if (!$this->rbac->hasPermission((int)$userId, $projectId, 'member.permission.override')) {
            return $this->error('Not allowed to manage permission overrides', 403);
        }
        if (!$this->roleService->canManageUserPermissions((int)$userId, $projectId, $memberId)) {
            return $this->error('You can only manage permission overrides for members ranked below you', 403);
        }

        $this->roleRepository->removeUserOverride($projectId, $memberId, $permissionCode);
        return $this->success(null, 'Override removed');
    }

    // ═══════════════════════════════════════════════════════════
    //  Effective Permissions Matrix (computed)
    // ═══════════════════════════════════════════════════════════

    #[OA\Get(
        path: '/project/{projectId}/member/{memberId}/effective-permissions',
        description: 'Возвращает computed matrix разрешений участника с источником (role / override / billing / business_rule)',
        summary: 'Computed matrix разрешений участника',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'memberId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Матрица разрешений',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'permission_code', type: 'string'),
                                new OA\Property(property: 'granted', type: 'boolean'),
                                new OA\Property(property: 'source', type: 'string', enum: ['role', 'override', 'billing', 'business_rule']),
                                new OA\Property(property: 'source_detail', type: 'string'),
                            ],
                            type: 'object',
                        )),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Проект или участник не найдены'),
        ],
    )]
    /**
     * GET /project/{projectId}/member/{memberId}/effective-permissions
     *
     * Returns computed matrix with source information for the admin UI.
     * @throws ValidationException
     * @throws NotFoundHttpException
     */
    public function actionEffectivePermissions(int $projectId, int $memberId): CollectionDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $project = $this->projectRepository->findById(new ProjectId($projectId));
        if (!$project) {
            throw new NotFoundHttpException('Project not found');
        }

        if ($memberId !== (int)$userId) {
            if (!$this->rbac->hasPermission((int)$userId, $projectId, 'member.permission.override')) {
                return $this->error('Not allowed to view another member permission matrix', 403);
            }
            if (!$this->roleService->canManageUserPermissions((int)$userId, $projectId, $memberId)) {
                return $this->error('You can only view permission matrices for members ranked below you', 403);
            }
        }
        if (!ProjectUserAR::findOne(['project_id' => $projectId, 'user_id' => $memberId, 'is_left' => 0])) {
            throw new NotFoundHttpException('Project member not found');
        }

        // Get all possible permission codes
        $allCodes = $this->getAllPermissionCodes();

        $items = [];

        foreach ($allCodes as $code) {
            $result = $this->computeEffectivePermission($project, $memberId, $code);
            $items[] = new EffectivePermissionDto(
                $code,
                $result['granted'],
                $result['source'],
                $result['detail'],
            );
        }

        return $this->collection($items);
    }

    // ═══════════════════════════════════════════════════════════
    //  Leave & Transfer
    // ═══════════════════════════════════════════════════════════

    #[OA\Post(
        path: '/project/{projectId}/leave',
        summary: 'Покинуть проект (выйти из участников)',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Участник покинул проект'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Недостаточно прав (последний админ / владелец)'),
        ],
    )]
    /**
     * POST /project/{projectId}/leave
     */
    public function actionLeave(int $projectId): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        if (!$this->rbac->hasPermission((int)$userId, $projectId, 'member.leave')) {
            return $this->error('Not allowed to leave this project', 403);
        }

        try {
            $this->membershipService->leave((int)$userId, $projectId);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 403);
        }

        return $this->success(null, 'You have left the project');
    }

    #[OA\Post(
        path: '/project/{projectId}/transfer',
        summary: 'Передать владение проектом другому участнику',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TransferOwnershipRequest')),
        tags: ['project-rbac'],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Владение передано'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Недостаточно прав'),
        ],
    )]
    /**
     * POST /project/{projectId}/transfer
     */
    public function actionTransfer(int $projectId): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $request = new TransferOwnershipRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        try {
            $this->membershipService->transferOwnership((int)$userId, (int)$request->new_owner_id, $projectId);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 403);
        }

        return $this->success(null, 'Ownership transferred');
    }

    // ═══════════════════════════════════════════════════════════
    //  Private helpers
    // ═══════════════════════════════════════════════════════════

    /**
     * Compute effective permission for a member in a project with detailed source.
     *
     * @param Project $project
     * @param int $memberId
     * @param string $permissionCode
     * @return array ['granted' => bool, 'source' => string, 'detail' => string]
     */
    private function computeEffectivePermission(Project $project, int $memberId, string $permissionCode): array
    {
        $projectId = $project->getId()->value();
        $ownerId = $project->getOwnerId()->value();

        // 1. Personal — only the owner has access.
        if ($project->getType()->isPersonal()) {
            return [
                'granted' => $memberId === $ownerId,
                'source'  => 'business_rule',
                'detail'  => 'Personal project — full access for owner',
            ];
        }

        // 2. Billing
        if (!$this->billingLimits->isFeatureAvailable($ownerId, $permissionCode)) {
            return [
                'granted' => false,
                'source'  => 'billing',
                'detail'  => 'Not available on current tariff plan',
            ];
        }

        // 3. Only the project-local role with slug 'admin' grants full access.
        $role = $this->getUserRole($memberId, $projectId);
        if ($role && $role->getSlug() === 'admin' && $role->isSystem()) {
            return [
                'granted' => true,
                'source'  => 'role',
                'detail'  => $role->getName() . ' (rank ' . $role->getRank()->value() . ')',
            ];
        }

        // 4. Role permission
        if ($role) {
            $rolePerms = $this->roleRepository->findRolePermissionCodes($role->getId());
            if (in_array($permissionCode, $rolePerms, true)) {
                return [
                    'granted' => true,
                    'source'  => 'role',
                    'detail'  => $role->getName() . ' role permission',
                ];
            }
        }

        // 5. Override
        $override = $this->roleRepository->findUserOverride($projectId, $memberId, $permissionCode);
        if ($override !== null) {
            return [
                'granted' => $override,
                'source'  => 'override',
                'detail'  => $override ? 'Individual override: granted' : 'Individual override: denied',
            ];
        }

        // 6. Default deny
        return [
            'granted' => false,
            'source'  => 'business_rule',
            'detail'  => 'Default deny — not assigned to role or override',
        ];
    }

    /**
     * Get role VO for a user in a project.
     */
    private function getUserRole(int $userId, int $projectId): ?ProjectRoleVO
    {
        $projectUser = ProjectUserAR::findOne([
            'project_id' => $projectId,
            'user_id' => $userId,
            'is_left' => 0,
        ]);
        if (!$projectUser || !$projectUser->role_id) {
            return null;
        }
        $role = $this->roleRepository->findRoleById((int)$projectUser->role_id);
        return $role !== null && $role->getProjectId() === $projectId ? $role : null;
    }

    /**
     * @return string[]
     */
    private function getAllPermissionCodes(): array
    {
        return $this->roleRepository->findAllPermissionCodes();
    }

    /**
     * Convert ProjectRole VO to a plain array for JSON response.
     */
    private function roleToArray(ProjectRoleVO $role): array
    {
        return [
            'id'         => $role->getId(),
            'project_id' => $role->getProjectId(),
            'name'       => $role->getName(),
            'slug'       => $role->getSlug(),
            'rank'       => $role->getRank()->value(),
            'is_system'  => $role->isSystem(),
            'is_preset'  => $role->isPreset(),
        ];
    }
}
