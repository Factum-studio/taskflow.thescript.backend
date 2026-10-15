<?php

declare(strict_types=1);

namespace modules\company\application\port;

interface ICompanyAccess
{
    /**
     * Может ли пользователь просматривать компанию.
     */
    public function canViewCompany(int $userId, int $companyId): bool;

    /**
     * Может ли пользователь управлять компанией (изменять данные).
     * Владелец/админ компании или глобальный админ.
     */
    public function canManageCompany(int $userId, int $companyId): bool;

    /**
     * Может ли пользователь архивировать/разархивировать компанию.
     */
    public function canArchiveCompany(int $userId, int $companyId): bool;

    /**
     * Может ли пользователь удалить компанию (только глобальный админ).
     */
    public function canDeleteCompany(int $userId, int $companyId): bool;

    /**
     * Может ли пользователь верифицировать компанию (только глобальный админ).
     */
    public function canVerifyCompany(int $userId): bool;

    /**
     * Может ли пользователь приглашать участников в компанию.
     */
    public function canInviteUser(int $userId, int $companyId): bool;

    /**
     * Может ли пользователь удалить участника из компании.
     */
    public function canRemoveMember(int $userId, int $targetUserId, int $companyId): bool;

    /**
     * Может ли пользователь изменить роль участника в компании.
     */
    public function canChangeMemberRole(int $userId, int $targetUserId, int $companyId): bool;

    /**
     * Может ли пользователь передать владение компанией.
     */
    public function canTransferOwnership(int $userId, int $companyId): bool;
}