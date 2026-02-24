<?php

namespace core\application\handler;

use core\application\query\GetCurrentUserQuery;
use core\domain\entity\User;
use core\domain\exception\UserNotFoundException;
use Yii;

final class GetCurrentUserQueryHandler
{
    public function handle(GetCurrentUserQuery $query): User
    {
        /**
         * @var \core\security\YiiIdentity $identity
         */
        $identity = Yii::$app->user->identity;

        if (!$identity) {
            throw new UserNotFoundException('Current user not found');
        }

        return $identity->getUser();
    }
}