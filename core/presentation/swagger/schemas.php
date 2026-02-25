<?php
/**
 * @OA\Schema(
 *     schema="Collection",
 *     required={"items"},
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(type="object"),
 *         description="Массив элементов коллекции"
 *     ),
 *     @OA\Property(
 *         property="_meta",
 *         type="object",
 *         description="Мета-информация о пагинации",
 *         @OA\Property(
 *             property="total",
 *             type="integer",
 *             description="Общее количество элементов",
 *             example=100
 *         ),
 *         @OA\Property(
 *             property="page",
 *             type="integer",
 *             description="Текущая страница",
 *             example=1
 *         ),
 *         @OA\Property(
 *             property="limit",
 *             type="integer",
 *             description="Количество элементов на странице",
 *             example=20
 *         ),
 *         @OA\Property(
 *             property="pages",
 *             type="integer",
 *             description="Общее количество страниц",
 *             example=5,
 *             nullable=true
 *         )
 *     )
 * )
 * @OA\Schema(
 *     schema="Item",
 *     @OA\Property(property="item", type="object")
 * )
 * @OA\Schema(
 *     schema="Error",
 *     @OA\Property(property="error", type="object",
 *         @OA\Property(property="code", type="integer"),
 *         @OA\Property(property="message", type="string"),
 *         @OA\Property(property="details", type="object", additionalProperties=true)
 *     )
 * )
 */
class MainSchemasDefinitions {}

/**
 * @OA\Schema(
 *     schema="Contact",
 *     required={"type", "value"},
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         enum={"tg", "phone", "email", "vk", "discord"},
 *         description="Тип контакта"
 *     ),
 *     @OA\Property(
 *         property="value",
 *         type="string",
 *         description="Значение контакта"
 *     ),
 *     @OA\Property(
 *         property="confirmed",
 *         type="boolean",
 *         description="Флаг подтверждения"
 *     )
 * )
 */
class ContactSchema {}

/**
 * @OA\Schema(
 *     schema="Post",
 *     required={"id", "name"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID должности"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Название должности"
 *     )
 * )
 */
class PostSchema {}

/**
 * @OA\Schema(
 *     schema="User",
 *     required={"id", "surname", "name", "dob"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID пользователя"
 *     ),
 *     @OA\Property(
 *         property="surname",
 *         type="string",
 *         description="Фамилия"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Имя"
 *     ),
 *     @OA\Property(
 *         property="patronymic",
 *         type="string",
 *         nullable=true,
 *         description="Отчество"
 *     ),
 *     @OA\Property(
 *         property="full_name",
 *         type="string",
 *         description="Полное имя (Фамилия Имя Отчество)"
 *     ),
 *     @OA\Property(
 *         property="dob",
 *         type="integer",
 *         format="timestamp",
 *         description="Дата рождения (timestamp)"
 *     ),
 *     @OA\Property(
 *         property="contacts",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Contact"),
 *         description="Контакты пользователя"
 *     ),
 *     @OA\Property(
 *         property="post",
 *         ref="#/components/schemas/Post",
 *         nullable=true,
 *         description="Должность пользователя"
 *     )
 * )
 */
class UserSchema {}

/**
 * @OA\Schema(
 *     schema="Task",
 *     required={"id", "title", "statusId", "priorityId", "createdBy", "boardId", "overdue", "createdAt", "updatedAt"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Написать документацию"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Подробное описание задачи..."),
 *     @OA\Property(property="statusId", type="integer", example=1),
 *     @OA\Property(property="priorityId", type="integer", example=2),
 *     @OA\Property(property="dueDate", type="string", format="date-time", nullable=true, example="2026-03-01 12:00:00"),
 *     @OA\Property(property="createdBy", type="integer", example=42),
 *     @OA\Property(property="assignedTo", type="integer", nullable=true, example=17),
 *     @OA\Property(property="boardId", type="integer", example=5),
 *     @OA\Property(property="parentId", type="integer", nullable=true, example=10),
 *     @OA\Property(property="overdue", type="boolean", example=false),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2026-02-25 10:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2026-02-25 11:30:00"),
 *     @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true, example=null)
 * )
 *
 * @OA\Schema(
 *     schema="CreateTaskRequest",
 *     required={"title", "statusId", "priorityId", "boardId"},
 *     @OA\Property(property="title", type="string", maxLength=255, example="Новая задача"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Описание"),
 *     @OA\Property(property="statusId", type="integer", example=1),
 *     @OA\Property(property="priorityId", type="integer", example=2),
 *     @OA\Property(property="dueDate", type="string", format="date-time", nullable=true, example="2026-03-01 12:00:00"),
 *     @OA\Property(property="boardId", type="integer", example=5),
 *     @OA\Property(property="assignedTo", type="integer", nullable=true, example=17),
 *     @OA\Property(property="parentId", type="integer", nullable=true, example=10)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateTaskRequest",
 *     @OA\Property(property="title", type="string", maxLength=255, nullable=true, example="Обновлённый заголовок"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Новое описание"),
 *     @OA\Property(property="statusId", type="integer", nullable=true, example=3),
 *     @OA\Property(property="priorityId", type="integer", nullable=true, example=1),
 *     @OA\Property(property="dueDate", type="string", format="date-time", nullable=true, example="2026-03-02 15:00:00"),
 *     @OA\Property(property="boardId", type="integer", nullable=true, example=6),
 *     @OA\Property(property="assignedTo", type="integer", nullable=true, example=18),
 *     @OA\Property(property="parentId", type="integer", nullable=true, example=11)
 * )
 *
 * @OA\Schema(
 *     schema="ChangeTaskStatusRequest",
 *     required={"statusId"},
 *     @OA\Property(property="statusId", type="integer", example=2)
 * )
 *
 * @OA\Schema(
 *     schema="AssignTaskRequest",
 *     @OA\Property(property="assignedTo", type="integer", nullable=true, example=19)
 * )
 */
class TaskSchemas {}