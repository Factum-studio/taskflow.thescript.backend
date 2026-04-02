<?php

namespace modules\projects\presentation\controller;

use core\application\dto\ErrorDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use modules\projects\application\command\AcceptInvitationCommand;
use modules\projects\application\command\CancelInvitationCommand;
use modules\projects\application\command\InviteUserCommand;
use modules\projects\application\handler\AcceptInvitationHandler;
use modules\projects\application\handler\CancelInvitationHandler;
use modules\projects\application\handler\InviteUserHandler;
use modules\projects\presentation\request\InviteUserRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\ServerErrorHttpException;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'project-invitations',
    description: 'Управление приглашениями в проект'
)]
class InvitationController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly InviteUserHandler $inviteUserHandler,
        private readonly AcceptInvitationHandler $acceptInvitationHandler,
        private readonly CancelInvitationHandler $cancelInvitationHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws ServerErrorHttpException
     */
    #[OA\Post(
        path: '/project/{id}/invite',
        summary: 'Отправить приглашение пользователю по email',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                ]
            )
        ),
        tags: ['project-invitations'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'ID проекта', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Приглашение отправлено'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Нет прав'),
            new OA\Response(response: 404, description: 'Проект не найден'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    public function actionInvite(int $id): SuccessDto|ErrorDto|array
    {
        $request = new InviteUserRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new InviteUserCommand(
            projectId: $id,
            invitedBy: $userId,
            jwtToken: $this->getUserIdentity()->getJwtToken(),
            email: $request->email,
            userId: $request->userId
        );

        try {
            $this->inviteUserHandler->handle($command);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to send invitation', 0, $e);
        }

        return $this->success(null, 'Invitation sent');
    }

    /**
     * @throws ServerErrorHttpException
     */
    #[OA\Post(
        path: '/invitation/accept',
        summary: 'Принять приглашение по токену',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token'],
                properties: [
                    new OA\Property(property: 'token', type: 'string', example: 'abc123...'),
                ]
            )
        ),
        tags: ['project-invitations'],
        responses: [
            new OA\Response(response: 200, description: 'Приглашение принято'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Приглашение не найдено'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    public function actionAccept(): SuccessDto|ErrorDto|array
    {
        $token = Yii::$app->request->post('token');
        if (!$token) {
            return $this->error('Token is required', 422);
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new AcceptInvitationCommand(
            $token,
            $userId,
            $this->getUserIdentity()->getJwtToken()
        );

        try {
            $this->acceptInvitationHandler->handle($command);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to accept invitation', 0, $e);
        }

        return $this->success(null, 'Invitation accepted');
    }

    /**
     * @throws ServerErrorHttpException
     */
    #[OA\Delete(
        path: '/project/{id}/invitation?email={email}',
        summary: 'Отменить приглашение по email',
        security: [['bearerAuth' => []]],
        tags: ['project-invitations'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'ID проекта', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'email', description: 'Email приглашённого', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Приглашение отменено'),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 403, description: 'Нет прав'),
            new OA\Response(response: 404, description: 'Приглашение не найдено')
        ]
    )]
    public function actionCancel(int $id, string $email): SuccessDto|ErrorDto|array
    {
        $email = Yii::$app->request->get('email');
        if (!$email) {
            return $this->error('Email parameter is required', 422);
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new CancelInvitationCommand(
            $id,
            $email,
            $userId,
            $this->getUserIdentity()->getJwtToken()
        );

        try {
            $this->cancelInvitationHandler->handle($command);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to cancel invitation', 0, $e);
        }

        return $this->success(null, 'Invitation cancelled');
    }
}