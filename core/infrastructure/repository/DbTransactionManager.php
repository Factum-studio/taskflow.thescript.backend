<?php

declare(strict_types=1);

namespace core\infrastructure\repository;

use core\application\port\ITransactionManager;
use Throwable;
use Yii;
use yii\db\Exception;

class DbTransactionManager implements ITransactionManager
{
    /**
     * @throws Throwable
     * @throws Exception
     */
    public function run(callable $operation): mixed
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $result = $operation();
            $transaction->commit();

            return $result;
        } catch (Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $e;
        }
    }
}
