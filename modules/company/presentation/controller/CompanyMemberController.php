<?php

declare(strict_types=1);

namespace modules\company\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\domain\exception\ValidationException;
use core\presentation\controller\BaseController;
use modules\company\application\assembler\CompanyMemberDtoAssembler;
use modules\company\application\command\ChangeMemberRoleCommand;
use modules\company\application\command\RemoveCompanyMemberCommand;
use modules\company\application\command\TransferOwnershipCommand;
use modules\company\application\handler\ChangeMemberRoleHandler;
use modules\company\application\handler\RemoveCompanyMemberHandler;
use modules\company\application\handler\TransferOwnershipHandler;
use modules\company\application\port\ICompanyAccess;
use modules\company\domain\repository\ICompanyMemberRepository;
use modules\company\domain\valueObject\CompanyId;
use modules\company\presentation\request\ChangeMemberRoleRequest;
use core\domain\valueObject\UserId;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use OpenApi\Attributes as OA;
use yii\web\UnauthorizedHttpException;

#[OA\Tag(
    name: 'companies',
    description: 'Управление компаниями',
)]
class CompanyMemberController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly ICompanyMemberRepository $memberRepository,
        private readonly CompanyMemberDtoAssembler $memberDtoAssembler,
        private readonly ICompanyAccess $companyAccess,
        private readonly ChangeMemberRoleHandler $changeMemberRoleHandler,
        private readonly RemoveCompanyMemberHandler $removeCompanyMemberHandler,
        private readonly TransferOwnershipHandler $transferOwnershipHandler,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws ValidationException
     */
    #[OA\Get(
        path: '/company/{companyId}/member',
        summary: 'Список участников компании',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'companyId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Успешный ответ'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Компания не найдена'),
        ],
    )]
    public function actionIndex(int $companyId): CollectionDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        if (!$this->companyAccess->canViewCompany((int)$userId, $companyId)) {
            return $this->error('You are not allowed to view this company', 403);
        }

        $members = $this->memberRepository->findByCompany(new CompanyId($companyId));
        return $this->collection($this->memberDtoAssembler->toDtoList($members));
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Put(
        path: '/company/{companyId}/member/{userId}/role',
        summary: 'Изменить роль участника компании',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'companyId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Роль изменена'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Компания или участник не найдены'),
        ],
    )]
    public function actionChangeRole(int $companyId, int $userId): SuccessDto|ErrorDto|array
    {
        $currentUserId = $this->getUserId();
        if (!$currentUserId) {
            return $this->error('User not authenticated', 401);
        }

        $request = new ChangeMemberRoleRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $command = new ChangeMemberRoleCommand(
            companyId: $companyId,
            userId: (int)$currentUserId,
            targetUserId: $userId,
            newRole: $request->role,
        );

        try {
            $this->changeMemberRoleHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'not a member')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            return $this->error($e->getMessage(), 403);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to change member role', 0, $e);
        }

        return $this->success(null, 'Member role changed');
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Delete(
        path: '/company/{companyId}/member/{userId}',
        summary: 'Удалить участника из компании',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'companyId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Участник удалён'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Компания или участник не найдены'),
        ],
    )]
    public function actionDelete(int $companyId, int $userId): SuccessDto|ErrorDto|array
    {
        $currentUserId = $this->getUserId();
        if (!$currentUserId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new RemoveCompanyMemberCommand(
            companyId: $companyId,
            userId: (int)$currentUserId,
            targetUserId: $userId,
        );

        try {
            $this->removeCompanyMemberHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'not a member')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            return $this->error($e->getMessage(), 403);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to remove member', 0, $e);
        }

        return $this->success(null, 'Member removed');
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Post(
        path: '/company/{companyId}/transfer/{userId}',
        summary: 'Передать владение компанией',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'companyId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Владение передано'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Компания или участник не найдены'),
        ],
    )]
    public function actionTransfer(int $companyId, int $userId): SuccessDto|ErrorDto|array
    {
        $currentUserId = $this->getUserId();
        if (!$currentUserId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new TransferOwnershipCommand(
            companyId: $companyId,
            userId: (int)$currentUserId,
            newOwnerId: $userId,
        );

        try {
            $this->transferOwnershipHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'not a member')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            return $this->error($e->getMessage(), 403);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to transfer ownership', 0, $e);
        }

        return $this->success(null, 'Ownership transferred');
    }
}
