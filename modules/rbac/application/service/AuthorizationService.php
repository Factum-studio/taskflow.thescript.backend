<?php

declare(strict_types=1);

namespace modules\rbac\application\service;

use core\domain\exception\PermissionDeniedException;
use core\security\YiiIdentity;
use modules\rbac\application\port\IAuthorizationService;
use modules\rbac\application\port\IPermissionRepository;
use Yii;

final class AuthorizationService implements IAuthorizationService
{
    /** @var array<int, string[]> */
    private array $permissionCache = [];

    public function __construct(
        private readonly IPermissionRepository $permissionRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function can(int $userId, string $permission, array $context = []): bool
    {
        $identity = Yii::$app->user->identity;
        if (!$identity instanceof YiiIdentity || (int)$identity->getId() !== $userId) {
            return false;
        }

        $required   = $this->resolveConcretePermission($permission, $context);
        $definition = $this->permissionRepository->findByCode($required);
        if ($definition === null) {
            return false;
        }

        $assigned = $this->getUserPermissions($userId);

        return $this->matches($assigned, $required);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function canForIdentity(
        YiiIdentity $identity,
        string $permission,
        array $context = [],
    ): bool {
        if ($identity->isUser()) {
            if ($identity->getId() === '' || !ctype_digit($identity->getId())) {
                return false;
            }

            return $this->can((int)$identity->getId(), $permission, $context);
        }

        if ($identity->getClientId() === '') {
            return false;
        }

        $required = $this->resolveClientPermission($permission, $context);
        $definition = $this->permissionRepository->findByCode($required);
        if ($definition === null) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $context
     * @throws PermissionDeniedException
     */
    public function authorizeForIdentity(
        YiiIdentity $identity,
        string $permission,
        array $context = [],
    ): void {
        if (!$this->canForIdentity($identity, $permission, $context)) {
            throw new PermissionDeniedException(
                sprintf('Permission denied: %s', $permission),
                ['Possible reasons' => ['The token subject or RBAC permission does not allow this operation.']],
            );
        }
    }

    /** @param array<string, mixed> $context */
    private function resolveClientPermission(string $permission, array $context): string
    {
        $mode = (string)($context['mode'] ?? 'any');

        if (in_array($mode, ['self-or-any', 'owner-only'], true)) {
            if (str_ends_with($permission, '.self')) {
                return substr($permission, 0, -5) . '.any';
            }

            if (str_ends_with($permission, '.any')) {
                return $permission;
            }

            return $permission . '.any';
        }

        return $permission;
    }

    /**
     * @param array<string, mixed> $context
     * @throws PermissionDeniedException
     */
    public function authorize(int $userId, string $permission, array $context = []): void
    {
        if (!$this->can($userId, $permission, $context)) {
            throw new PermissionDeniedException(
                sprintf('Permission denied: %s', $permission),
                ["Possible reasons" => ["You are not logged in, your token is weak, or your role does not match."]],
            );
        }
    }

    /**
     * Поддерживаемый контекст:
     * - mode=self-or-any, subjectUserId=<int>
     * - mode=owner-only, subjectUserId=<int>
     * - mode=any
     *
     * Таким образом, логическое разрешение `users.read` становится либо
     * `users.read.self` либо `users.read.any`.
     *
     * @param array<string, mixed> $context
     */
    private function resolveConcretePermission(string $permission, array $context): string
    {
        $mode = (string)($context['mode'] ?? 'any');

        if ($mode === 'self-or-any') {
            if (!array_key_exists('subjectResolved', $context) || $context['subjectResolved'] === true) {
                if (isset($context['subjectUserId'])) {
                    return (int)$context['subjectUserId'] === (int)$context['userId']
                        ? $permission . '.self'
                        : $permission . '.any';
                }

                return $permission . '.any';
            }

            // Не удалось определить владельца объявленного ресурса. Ошибка закрытия.
            return '__unresolvable_subject__';
        }

        if ($mode === 'owner-only') {
            return $permission . '.self';
        }

        return $permission;
    }

    /** @return string[] */
    private function getUserPermissions(int $userId): array
    {
        return $this->permissionCache[$userId]
            ??= $this->permissionRepository->findCodesByUserId($userId);
    }

    /** @param string[] $assigned */
    private function matches(array $assigned, string $required): bool
    {
        if (in_array('*', $assigned, true)) {
            return true;
        }

        if (in_array($required, $assigned, true)) {
            return true;
        }

        $parts = explode('.', $required);
        while (count($parts) > 1) {
            array_pop($parts);
            if (in_array(implode('.', $parts) . '.*', $assigned, true)) {
                return true;
            }
        }

        return false;
    }
}
