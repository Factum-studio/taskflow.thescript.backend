<?php

namespace core\infrastructure\repository;

use core\application\port\ILocalUserRepository;
use core\infrastructure\persistence\UserAR;
use DateTimeImmutable;
use RuntimeException;
use yii\db\Exception;

class DbLocalUserRepository implements ILocalUserRepository
{
    public function exists(int $userId): bool
    {
        return UserAR::find()->where(['user_id' => $userId])->exists();
    }

    /**
     * @throws Exception
     */
    public function create(int $userId, string $email, ?string $post = null, ?int $companyId = null): void
    {
        $ar             = new UserAR();
        $ar->user_id    = $userId;
        $ar->email      = $email ?: null;
        $ar->company_id = $companyId;
        $ar->post       = $post;
        if (!$ar->save()) {
            throw new RuntimeException('Failed to create local user record');
        }
    }

    /**
     * @throws \Exception
     */
    public function getCreatedAt(int $userId): ?DateTimeImmutable
    {
        $ar = UserAR::find()->where(['user_id' => $userId])->one();
        if (!$ar) {
            return null;
        }
        return new DateTimeImmutable($ar->created_at);
    }

    public function getEmail(int $userId): ?string
    {
        $ar = UserAR::find()->where(['user_id' => $userId])->one();
        return $ar?->email;
    }

    public function getPost(int $userId): ?string
    {
        $ar = UserAR::find()->where(['user_id' => $userId])->one();
        return $ar?->post;
    }

    public function getCompanyId(int $userId): ?int
    {
        $ar = UserAR::find()->where(['user_id' => $userId])->one();
        return $ar?->company_id;
    }
}