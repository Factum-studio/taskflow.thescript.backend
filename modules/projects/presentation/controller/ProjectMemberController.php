<?php

namespace modules\projects\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\presentation\controller\BaseController;
use modules\projects\application\command\AddProjectMemberCommand;
use modules\projects\application\command\ChangeProjectMemberRoleCommand;
use modules\projects\application\command\RemoveProjectMemberCommand;
use modules\projects\application\handler\AddProjectMemberHandler;
use modules\projects\application\handler\ChangeProjectMemberRoleHandler;
use modules\projects\application\handler\ListProjectMembersHandler;
use modules\projects\application\handler\RemoveProjectMemberHandler;
use modules\projects\application\query\ListProjectMembersQuery;
use modules\projects\presentation\request\AddProjectMemberRequest;
use modules\projects\presentation\request\ChangeProjectMemberRoleRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ProjectMemberController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly AddProjectMemberHandler $addMemberHandler,
        private readonly RemoveProjectMemberHandler $removeMemberHandler,
        private readonly ChangeProjectMemberRoleHandler $changeRoleHandler,
        private readonly ListProjectMembersHandler $listMembersHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionIndex(int $projectId): CollectionDto|array
    {
        $query = new ListProjectMembersQuery($projectId);
        try {
            $members = $this->listMembersHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->collection($members);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionCreate(int $projectId): ErrorDto|ItemDto|array
    {
        $request = new AddProjectMemberRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new AddProjectMemberCommand(
            projectId: $projectId,
            userId: $request->userId,
            role: $request->role,
            addedBy: $userId
        );

        try {
            $memberDto = $this->addMemberHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to add member', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to add member', 0, $e);
        }

        return $this->item($memberDto);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdateRole(int $projectId, int $userId): ErrorDto|ItemDto|array
    {
        $request = new ChangeProjectMemberRoleRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $currentUserId = $this->getUserId();
        if (!$currentUserId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new ChangeProjectMemberRoleCommand(
            projectId: $projectId,
            userId: $userId,
            newRole: $request->role,
            changedBy: $currentUserId
        );

        try {
            $memberDto = $this->changeRoleHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found') || strpos($e->getMessage(), 'not a member') !== false) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to change role', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to change role', 0, $e);
        }

        return $this->item($memberDto);
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionDelete(int $projectId, int $userId): SuccessDto|ErrorDto|array
    {
        $currentUserId = $this->getUserId();
        if (!$currentUserId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new RemoveProjectMemberCommand($projectId, $userId, $currentUserId);

        try {
            $this->removeMemberHandler->handle($command);
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'not a member')) {
                throw new NotFoundHttpException($e->getMessage());
            }
            if (str_contains($e->getMessage(), 'not allowed')) {
                return $this->error($e->getMessage(), 403);
            }
            throw new ServerErrorHttpException('Failed to remove member', 0, $e);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'projects');
            throw new ServerErrorHttpException('Failed to remove member', 0, $e);
        }

        return $this->success(null, 'Member removed');
    }
}