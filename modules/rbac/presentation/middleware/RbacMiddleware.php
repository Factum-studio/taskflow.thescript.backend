<?php

declare(strict_types=1);

namespace modules\rbac\presentation\middleware;

use core\domain\exception\PermissionDeniedException;
use core\security\YiiIdentity;
use modules\rbac\application\port\IAuthorizationService;
use modules\rbac\application\port\ISubjectResolver;
use modules\rbac\application\port\IRoleAssignmentPolicy;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\UnauthorizedHttpException;

final class RbacMiddleware
{
    /**
     * @param array<int, string> $publicRoutes
     * @param array<int, array<string, mixed>> $permissionRules
     */
    public function __construct(
        private readonly IAuthorizationService $authorization,
        private readonly ISubjectResolver $subjectResolver,
        private readonly IRoleAssignmentPolicy $roleAssignmentPolicy,
        private readonly array $publicRoutes,
        private readonly array $permissionRules,
    ) {
    }

    /**
     * @throws UnauthorizedHttpException
     * @throws InvalidConfigException
     * @throws PermissionDeniedException
     */
    public function handle(): void
    {
        $request = Yii::$app->request;
        $method = strtoupper($request->getMethod());

        if ($method === 'OPTIONS') {
            return;
        }

        $path = '/' . ltrim($request->getPathInfo(), '/');

        if ($this->isPublic($method, $path)) {
            return;
        }

        $matchedRule = $this->findRule($method, $path);

        if ($matchedRule === null) {
            throw new PermissionDeniedException(
                'RBAC policy is not defined for this route',
            );
        }

        $identity = Yii::$app->user->identity;
        if (!$identity instanceof YiiIdentity) {
            throw new UnauthorizedHttpException('Authentication required');
        }

        $context = $this->buildContext($matchedRule, $path);

        $this->authorization->authorizeForIdentity(
            $identity,
            (string)$matchedRule['permission'],
            $context,
        );

        $actorUserId = $identity->isUser() && ctype_digit($identity->getId())
            ? (int)$identity->getId()
            : 0;

        $policy = $matchedRule['policy'] ?? null;
        if ($policy === 'role-assignment') {
            $this->authorizeRoleAssignment($actorUserId, $method, $path);
        }
    }

    /**
     * @throws PermissionDeniedException
     */
    private function authorizeRoleAssignment(int $actorUserId, string $method, string $path): void
    {
        if ($method === 'POST') {
            $targetUserId = (int)Yii::$app->request->getBodyParam('userId', 0);
            $targetRoleId = (int)Yii::$app->request->getBodyParam('roleId', 0);

            if (!$this->roleAssignmentPolicy->canAssign($actorUserId, $targetUserId, $targetRoleId)) {
                throw new PermissionDeniedException('Role hierarchy violation');
            }

            return;
        }

        if ($method === 'DELETE') {
            $matches = [];
            $userRoleId = preg_match('~/([0-9]+)$~', $path, $matches) === 1 ? (int)$matches[1] : 0;

            if (!$this->roleAssignmentPolicy->canRemove($actorUserId, $userRoleId)) {
                throw new PermissionDeniedException('Role hierarchy violation');
            }
        }
    }

    /** @param array<string, mixed> $rule */
    private function buildContext(array $rule, string $path): array
    {
        $identity = Yii::$app->user->identity;
        $context = ['userId' => $identity instanceof YiiIdentity && $identity->isUser() ? (int)$identity->getId() : null];

        $subject = $rule['subject'] ?? null;
        if (!is_array($subject)) {
            return $context;
        }

        $source = (string)($subject['source'] ?? '');
        $key = (string)($subject['key'] ?? '');

        $subjectUserId = null;
        $subjectResolved = true;

        if ($source === 'path') {
            $matches = [];
            if (isset($rule['pattern']) && preg_match($rule['pattern'], $path, $matches) === 1) {
                $subjectUserId = isset($matches[$key]) ? (int)$matches[$key] : null;
            }
        } elseif ($source === 'query') {
            $value = Yii::$app->request->get($key);
            $subjectUserId = is_numeric($value) ? (int)$value : null;
        } elseif ($source === 'body') {
            $value = Yii::$app->request->getBodyParam($key);
            $subjectUserId = is_numeric($value) ? (int)$value : null;
        } elseif ($source === 'owner') {
            $id = 0;
            $matches = [];
            if (isset($rule['pattern'], $rule['ownerKey']) && preg_match((string)$rule['pattern'], $path, $matches) === 1) {
                $captured = $matches[(string)$rule['ownerKey']] ?? null;
                if (is_numeric($captured)) {
                    $id = (int)$captured;
                }
            }

            if ($id === 0) {
                $id = preg_match('~/([^/]+)$~', $path, $matches) === 1 ? (int)$matches[1] : 0;
            }

            $subjectUserId = $this->subjectResolver->resolveOwner([
                'resource' => $key,
                'id' => $id,
            ]);
            $subjectResolved = $subjectUserId !== null;
        }

        if ($source !== '' && $subjectUserId !== null) {
            $context['subjectUserId'] = $subjectUserId;
        }

        if ($source !== '') {
            $context['subjectResolved'] = $subjectResolved;
        }

        $context['mode'] = (string)($subject['mode'] ?? 'any');

        return $context;
    }

    private function isPublic(string $method, string $path): bool
    {
        foreach ($this->publicRoutes as $route) {
            [$publicMethod, $publicPath] = array_pad(explode(' ', $route, 2), 2, '');
            if (strtoupper($publicMethod) !== $method) {
                continue;
            }

            if (
                $publicPath === '*' ||
                $publicPath === $path ||
                (str_ends_with($publicPath, '*') &&
                    str_starts_with($path, rtrim($publicPath, '*')))
            ) {
                return true;
            }

            if (preg_match('/<[^>]+>/', $publicPath)) {
                $pattern = '#^' . preg_replace('/<[^>]+>/', '[^/]+', $publicPath) . '$#';
                if (preg_match($pattern, $path) === 1) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @return array<string, mixed>|null */
    private function findRule(string $method, string $path): ?array
    {
        foreach ($this->permissionRules as $rule) {
            if (strtoupper((string)($rule['method'] ?? '')) !== $method) {
                continue;
            }

            $pattern = (string)($rule['pattern'] ?? '');
            if ($pattern !== '' && preg_match($pattern, $path) === 1) {
                return $rule;
            }
        }

        return null;
    }
}
