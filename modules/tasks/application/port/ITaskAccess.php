<?php

namespace modules\tasks\application\port;

interface ITaskAccess
{
    /**
     * Возвращает ID проектов, которым принадлежит колонка.
     * Для системных колонок может вернуть пустой массив.
     *
     * @param int $columnId
     * @return int[]
     */
    public function getProjectIdsByColumnId(int $columnId): array;

    /**
     * Может ли пользователь просматривать задачу.
     *
     * @param int $userId
     * @param int $taskId
     * @return bool
     */
    public function canViewTask(int $userId, int $taskId): bool;

    /**
     * Может ли пользователь создать задачу на доске.
     *
     * @param int $userId
     * @param int $boardId
     * @return bool
     */
    public function canCreateTask(int $userId, int $boardId): bool;

    /**
     * Может ли пользователь обновить задачу.
     *
     * @param int $userId
     * @param int $taskId
     * @return bool
     */
    public function canUpdateTask(int $userId, int $taskId): bool;

    /**
     * Может ли пользователь мягко удалить задачу.
     *
     * @param int $userId
     * @param int $taskId
     * @return bool
     */
    public function canDeleteTask(int $userId, int $taskId): bool;

    /**
     * Может ли пользователь жёстко удалить задачу.
     *
     * @param int $userId
     * @param int $taskId
     * @return bool
     */
    public function canHardDeleteTask(int $userId, int $taskId): bool;

    /**
     * Может ли пользователь восстановить мягко удалённую задачу.
     *
     * @param int $userId
     * @param int $taskId
     * @return bool
     */
    public function canRestoreTask(int $userId, int $taskId): bool;

    /**
     * Может ли пользователь назначить исполнителя на задачу.
     *
     * @param int $userId
     * @param int $taskId
     * @param int $assigneeId
     * @return bool
     */
    public function canAssignTask(int $userId, int $taskId, int $assigneeId): bool;

    /**
     * Может ли пользователь комментировать задачу.
     *
     * @param int $userId
     * @param int $taskId
     * @return bool
     */
    public function canCommentOnTask(int $userId, int $taskId): bool;

    /**
     * Может ли пользователь добавлять стикер к задаче.
     *
     * @param int $userId
     * @param int $taskId
     * @return bool
     */
    public function canAddStickerToTask(int $userId, int $taskId): bool;

    /**
     * Может ли пользователь создать колонку на доске.
     *
     * @param int $userId
     * @param int $boardId
     * @return bool
     */
    public function canCreateColumn(int $userId, int $boardId): bool;

    /**
     * Может ли пользователь обновить колонку.
     *
     * @param int $userId
     * @param int $columnId
     * @return bool
     */
    public function canUpdateColumn(int $userId, int $columnId): bool;

    /**
     * Может ли пользователь удалить колонку (если в ней нет задач).
     *
     * @param int $userId
     * @param int $columnId
     * @return bool
     */
    public function canDeleteColumn(int $userId, int $columnId): bool;

    /**
     * Может ли пользователь переместить задачу в указанную колонку.
     *
     * @param int $userId
     * @param int $taskId
     * @param int $columnId
     * @return bool
     */
    public function canMoveTaskToColumn(int $userId, int $taskId, int $columnId): bool;
}