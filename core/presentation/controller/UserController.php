<?php

namespace core\presentation\controller;

use core\application\dto\ItemDto;
use core\application\dto\UserDto;
use core\application\handler\GetCurrentUserQueryHandler;
use core\application\query\GetCurrentUserQuery;
use core\domain\exception\UserNotFoundException;
use OpenApi\Attributes as OA;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

final class UserController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly GetCurrentUserQueryHandler $getCurrentUserHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionMe(): ItemDto
    {
        try {
            $user = $this->getCurrentUserHandler->handle(new GetCurrentUserQuery());

            return $this->item(UserDto::fromEntity($user));

        } catch (UserNotFoundException $e) {
            throw new NotFoundHttpException('User not found');
        }
    }
}