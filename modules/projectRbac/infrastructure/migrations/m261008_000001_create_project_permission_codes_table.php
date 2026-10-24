<?php

declare(strict_types=1);

namespace modules\projectRbac\infrastructure\migrations;

use yii\db\Migration;

/**
 * Creates project_permission_codes catalog table and seeds all permission codes.
 */
class m261008_000001_create_project_permission_codes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%project_permission_codes}}', [
            'code' => $this->string(100)->notNull(),
            'group' => $this->string(50)->notNull(),
            'description' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(`code`)',
        ]);

        $this->seedPermissions();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%project_permission_codes}}');
    }

    private function seedPermissions(): void
    {
        $permissions = [
            // ── Tasks ──
            ['task.view',            'task',    'Просмотр задачи'],
            ['task.create',          'task',    'Создание задачи'],
            ['task.update',          'task',    'Редактирование задачи'],
            ['task.delete',          'task',    'Мягкое удаление задачи'],
            ['task.hard_delete',     'task',    'Физическое удаление задачи'],
            ['task.restore',         'task',    'Восстановление мягко удалённой задачи'],
            ['task.move',            'task',    'Перемещение задачи между колонками'],
            ['task.assign',          'task',    'Назначение исполнителя на задачу'],
            ['task.priority.set',    'task',    'Установка приоритета задачи'],
            ['task.sticker.attach',  'task',    'Прикрепление стикера к задаче'],
            ['task.sticker.detach',  'task',    'Открепление стикера от задачи'],

            // ── Comments ──
            ['comment.create',      'comment', 'Создание комментария'],
            ['comment.update',      'comment', 'Редактирование своего комментария'],
            ['comment.delete.self', 'comment', 'Удаление своего комментария'],
            ['comment.delete.any',  'comment', 'Удаление любых комментариев'],

            // ── Stickers ──
            ['sticker.create',      'sticker', 'Создание стикера (тега/метки)'],
            ['sticker.update',      'sticker', 'Редактирование стикера'],
            ['sticker.delete',      'sticker', 'Удаление стикера'],

            // ── Columns / Statuses ──
            ['column.create',       'column',  'Создание колонки (статуса)'],
            ['column.update',       'column',  'Редактирование колонки'],
            ['column.delete',       'column',  'Удаление колонки'],
            ['column.reorder',      'column',  'Изменение порядка колонок'],

            // ── Members ──
            ['member.invite',               'member', 'Приглашение участников в проект'],
            ['member.add',                  'member', 'Прямое добавление участника (без приглашения)'],
            ['member.remove',               'member', 'Удаление участника из проекта'],
            ['member.role.change',          'member', 'Изменение роли участника'],
            ['member.permission.override',  'member', 'Управление индивидуальными разрешениями участника'],
            ['member.leave',                'member', 'Выход из проекта (себя)'],

            // ── Project ──
            ['project.settings.manage', 'project', 'Управление настройками проекта'],
            ['project.rename',          'project', 'Переименование проекта'],
            ['project.delete',          'project', 'Удаление проекта'],

            // ── Boards ──
            ['board.view',             'board', 'Просмотр доски'],
            ['board.create',           'board', 'Создание доски'],
            ['board.update',           'board', 'Редактирование доски'],
            ['board.delete',           'board', 'Удаление доски'],
            ['board.reorder',          'board', 'Изменение порядка досок'],
            ['board.settings.manage',  'board', 'Управление настройками доски'],

            // ── Project Roles ──
            ['role.create',             'role', 'Создание новой роли (пресета)'],
            ['role.update',             'role', 'Редактирование роли'],
            ['role.delete',             'role', 'Удаление роли (пресета)'],
            ['role.permissions.manage', 'role', 'Управление разрешениями, привязанными к роли'],
        ];

        $this->batchInsert(
            '{{%project_permission_codes}}',
            ['code', 'group', 'description'],
            $permissions,
        );
    }
}
