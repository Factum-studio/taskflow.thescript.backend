<?php

declare(strict_types=1);

namespace modules\company\presentation\controller;

use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use modules\company\application\command\AcceptInvitationCommand;
use modules\company\application\command\CancelInvitationCommand;
use modules\company\application\command\InviteUserCommand;
use modules\company\application\handler\AcceptInvitationHandler;
use modules\company\application\handler\CancelInvitationHandler;
use modules\company\application\handler\InviteUserHandler;
use modules\company\presentation\request\AcceptInvitationRequest;
use modules\company\presentation\request\InviteCompanyMemberRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'companies',
    description: 'Управление компаниями',
)]
class CompanyInvitationController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly InviteUserHandler $inviteUserHandler,
        private readonly AcceptInvitationHandler $acceptInvitationHandler,
        private readonly CancelInvitationHandler $cancelInvitationHandler,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Post(
        path: '/company/{id}/invite',
        summary: 'Пригласить пользователя в компанию по email',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Приглашение отправлено'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Компания не найдена'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
    public function actionInvite(int $id): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $request = new InviteCompanyMemberRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $command = new InviteUserCommand(
            companyId: $id,
            invitedBy: (int)$userId,
            email: $request->email,
        );

        try {
            $this->inviteUserHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to invite user', 0, $e);
        }

        return $this->success(null, 'Invitation sent');
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Post(
        path: '/company/invitation/accept',
        summary: 'Принять приглашение в компанию',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        responses: [
            new OA\Response(response: 200, description: 'Приглашение принято'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Приглашение не найдено'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
    public function actionAccept(): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $request = new AcceptInvitationRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $command = new AcceptInvitationCommand(
            token: $request->token,
            userId: (int)$userId,
        );

        try {
            $this->acceptInvitationHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to accept invitation', 0, $e);
        }

        return $this->success(null, 'Invitation accepted');
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    #[OA\Delete(
        path: '/company/{companyId}/invitation/{invitationId}',
        summary: 'Отменить приглашение в компанию',
        security: [['bearerAuth' => []], ['accessTokenCookie' => []]],
        tags: ['companies'],
        parameters: [
            new OA\Parameter(name: 'companyId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'invitationId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Приглашение отменено'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Доступ запрещён'),
            new OA\Response(response: 404, description: 'Приглашение не найдено'),
        ],
    )]
    public function actionCancel(int $companyId, int $invitationId): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new CancelInvitationCommand(
            companyId: $companyId,
            userId: (int)$userId,
            invitationId: $invitationId,
        );

        try {
            $this->cancelInvitationHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'company');
            throw new ServerErrorHttpException('Failed to cancel invitation', 0, $e);
        }

        return $this->success(null, 'Invitation cancelled');
    }
}
