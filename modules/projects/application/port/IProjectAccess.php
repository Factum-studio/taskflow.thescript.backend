<?php

namespace modules\projects\application\port;

interface IProjectAccess
{
    /**
     * Возвращает ID проектов, к которым пользователь имеет доступ.
     *
     * @param int $userId
     * @return int[]
     */
    public function getUserProjectIds(int $userId): array;

    /**
     * Возвращает ID досок, принадлежащих проекту.
     *
     * @param int $projectId
     * @return int[]
     */
    public function getProjectBoardIds(int $projectId): array;

    /**
     * Возвращает ID проекта, которому принадлежит доска.
     *
     * @param int $boardId
     * @return int|null
     */
    public function getProjectIdByBoardId(int $boardId): ?int;

    /**
     * Может ли пользователь просматривать проект.
     *
     * @param int $userId
     * @param int $projectId
     * @return bool
     */
    public function canViewProject(int $userId, int $projectId): bool;

    /**
     * Может ли пользователь просматривать доску.
     *
     * @param int $userId
     * @param int $boardId
     * @return bool
     */
    public function canViewBoard(int $userId, int $boardId): bool;

    /**
     * Может ли пользователь управлять другим участником (изменять роль и т.п.).
     *
     * @param int $userId
     * @param int $targetUserId
     * @param int $projectId
     * @return bool
     */
    public function canManageUser(int $userId, int $targetUserId, int $projectId): bool;

    /**
     * Может ли пользователь управлять настройками проекта.
     *
     * @param int $userId
     * @param int $projectId
     * @return bool
     */
    public function canManageProject(int $userId, int $projectId): bool;

    /**
     * Может ли пользователь управлять настройками доски.
     *
     * @param int $userId
     * @param int $boardId
     * @return bool
     */
    public function canManageBoard(int $userId, int $boardId): bool;

    /**
     * Может ли пользователь приглашать участников в проект.
     *
     * @param int $userId
     * @param int $projectId
     * @return bool
     */
    public function canInviteUser(int $userId, int $projectId): bool;

    /**
     * Может ли пользователь удалять другого участника из проекта.
     *
     * @param int $userId
     * @param int $targetUserId
     * @param int $projectId
     * @return bool
     */
    public function canRemoveUser(int $userId, int $targetUserId, int $projectId): bool;

    /**
     * Может ли пользователь удалить проект.
     *
     * @param int $userId
     * @param int $projectId
     * @return bool
     */
    public function canDeleteProject(int $userId, int $projectId): bool;

    /**
     * Может ли пользователь удалить доску.
     *
     * @param int $userId
     * @param int $boardId
     * @return bool
     */
    public function canDeleteBoard(int $userId, int $boardId): bool;

    /**
     * Может ли пользователь создать доску в проекте (с учётом лимитов).
     *
     * @param int $userId
     * @param int $projectId
     * @return bool
     */
    public function canCreateBoard(int $userId, int $projectId): bool;

    /**
     * Может ли пользователь создать новый проект (с учётом лимитов).
     *
     * @param int $userId
     * @return bool
     */
    public function canCreateProject(int $userId): bool;
}