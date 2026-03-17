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
 *     required={"id", "title", "columnId", "priorityId", "createdBy", "boardId", "overdue", "createdAt", "updatedAt"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Написать документацию"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Подробное описание задачи..."),
 *     @OA\Property(property="columnId", type="integer", example=5),
 *     @OA\Property(property="priorityId", type="integer", example=2),
 *     @OA\Property(property="dueDate", type="string", format="date-time", nullable=true, example="2026-03-01 12:00:00"),
 *     @OA\Property(property="plannedStart", type="string", format="date-time", nullable=true, example="2026-03-01 09:00:00"),
 *     @OA\Property(property="plannedEnd", type="string", format="date-time", nullable=true, example="2026-03-01 18:00:00"),
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
 *     required={"title", "columnId", "priorityId", "boardId"},
 *     @OA\Property(property="title", type="string", maxLength=255, example="Новая задача"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Описание"),
 *     @OA\Property(property="columnId", type="integer", example=5),
 *     @OA\Property(property="priorityId", type="integer", example=2),
 *     @OA\Property(property="dueDate", type="string", format="date-time", nullable=true, example="2026-03-01 12:00:00"),
 *     @OA\Property(property="plannedStart", type="string", format="date-time", nullable=true, example="2026-03-01 09:00:00"),
 *     @OA\Property(property="plannedEnd", type="string", format="date-time", nullable=true, example="2026-03-01 18:00:00"),
 *     @OA\Property(property="boardId", type="integer", example=5),
 *     @OA\Property(property="assignedTo", type="integer", nullable=true, example=17),
 *     @OA\Property(property="parentId", type="integer", nullable=true, example=10)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateTaskRequest",
 *     @OA\Property(property="title", type="string", maxLength=255, nullable=true, example="Обновлённый заголовок"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Новое описание"),
 *     @OA\Property(property="columnId", type="integer", nullable=true, example=6),
 *     @OA\Property(property="priorityId", type="integer", nullable=true, example=1),
 *     @OA\Property(property="dueDate", type="string", format="date-time", nullable=true, example="2026-03-02 15:00:00"),
 *     @OA\Property(property="plannedStart", type="string", format="date-time", nullable=true, example="2026-03-02 09:00:00"),
 *     @OA\Property(property="plannedEnd", type="string", format="date-time", nullable=true, example="2026-03-02 18:00:00"),
 *     @OA\Property(property="boardId", type="integer", nullable=true, example=6),
 *     @OA\Property(property="assignedTo", type="integer", nullable=true, example=18),
 *     @OA\Property(property="parentId", type="integer", nullable=true, example=11)
 * )
 *
 * @OA\Schema(
 *     schema="MoveTaskToColumnRequest",
 *     required={"columnId"},
 *     @OA\Property(property="columnId", type="integer", example=7)
 * )
 *
 * @OA\Schema(
 *     schema="AssignTaskRequest",
 *     @OA\Property(property="assignedTo", type="integer", nullable=true, example=19)
 * )
 *
 * @OA\Schema(
 *     schema="BoardColumn",
 *     required={"id", "boardId", "name", "label", "sortOrder", "isActive", "isFinal"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="boardId", type="integer", example=5),
 *     @OA\Property(property="name", type="string", example="in_progress"),
 *     @OA\Property(property="label", type="string", example="В работе"),
 *     @OA\Property(property="sortOrder", type="integer", example=20),
 *     @OA\Property(property="isActive", type="boolean", example=true),
 *     @OA\Property(property="isFinal", type="boolean", example=false),
 *     @OA\Property(property="color", type="string", nullable=true, example="#ff9900"),
 *     @OA\Property(property="workflowId", type="integer", nullable=true, example=1),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2026-03-18 10:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2026-03-18 10:00:00")
 * )
 *
 * @OA\Schema(
 *     schema="CreateBoardColumnRequest",
 *     required={"name", "label"},
 *     @OA\Property(property="name", type="string", maxLength=50, example="review"),
 *     @OA\Property(property="label", type="string", maxLength=255, example="Ревью"),
 *     @OA\Property(property="sortOrder", type="integer", nullable=true, example=30),
 *     @OA\Property(property="isActive", type="boolean", nullable=true, example=true),
 *     @OA\Property(property="isFinal", type="boolean", nullable=true, example=false),
 *     @OA\Property(property="color", type="string", maxLength=20, nullable=true, example="#00ff00"),
 *     @OA\Property(property="workflowId", type="integer", nullable=true, example=1)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateBoardColumnRequest",
 *     @OA\Property(property="name", type="string", maxLength=50, nullable=true, example="done"),
 *     @OA\Property(property="label", type="string", maxLength=255, nullable=true, example="Готово"),
 *     @OA\Property(property="sortOrder", type="integer", nullable=true, example=40),
 *     @OA\Property(property="isActive", type="boolean", nullable=true, example=false),
 *     @OA\Property(property="isFinal", type="boolean", nullable=true, example=true),
 *     @OA\Property(property="color", type="string", maxLength=20, nullable=true, example="#ff0000"),
 *     @OA\Property(property="workflowId", type="integer", nullable=true, example=2)
 * )
 *
 * @OA\Schema(
 *     schema="ReorderBoardColumnsRequest",
 *     required={"orderedIds"},
 *     @OA\Property(
 *         property="orderedIds",
 *         type="array",
 *         items=@OA\Items(type="integer"),
 *         example={3, 1, 2}
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="TaskPriority",
 *     required={"id", "value", "label"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="value", type="integer", example=2),
 *     @OA\Property(property="label", type="string", example="Средний"),
 *     @OA\Property(property="color", type="string", nullable=true, example="#ffff00")
 * )
 *
 * @OA\Schema(
 *     schema="Comment",
 *     required={"id", "taskId", "userId", "content", "createdAt", "updatedAt"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="taskId", type="integer", example=42),
 *     @OA\Property(property="userId", type="integer", example=17),
 *     @OA\Property(property="content", type="string", example="Это комментарий"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2026-02-26 12:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2026-02-26 12:00:00")
 * )
 *
 * @OA\Schema(
 *     schema="AddCommentRequest",
 *     required={"content"},
 *     @OA\Property(property="content", type="string", example="Новый комментарий")
 * )
 *
 * @OA\Schema(
 *     schema="UpdateCommentRequest",
 *     required={"content"},
 *     @OA\Property(property="content", type="string", example="Обновлённый комментарий")
 * )
 *
 * @OA\Schema(
 *     schema="Sticker",
 *     required={"id", "name", "type", "createdBy", "createdAt", "updatedAt"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Баг"),
 *     @OA\Property(property="type", type="string", enum={"system", "user"}, example="system"),
 *     @OA\Property(property="projectId", type="integer", nullable=true, example=5),
 *     @OA\Property(property="data", type="object", nullable=true, example={"time": 120}),
 *     @OA\Property(property="color", type="string", nullable=true, example="#ff0000"),
 *     @OA\Property(property="createdBy", type="integer", example=1),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2026-02-26 12:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2026-02-26 12:00:00")
 * )
 *
 * @OA\Schema(
 *     schema="CreateStickerRequest",
 *     required={"name", "type"},
 *     @OA\Property(property="name", type="string", example="Срочно"),
 *     @OA\Property(property="type", type="string", enum={"system", "user"}, example="user"),
 *     @OA\Property(property="projectId", type="integer", nullable=true, example=5),
 *     @OA\Property(property="data", type="object", nullable=true, example={"time": 30}),
 *     @OA\Property(property="color", type="string", nullable=true, example="#00ff00")
 * )
 *
 * @OA\Schema(
 *     schema="UpdateStickerRequest",
 *     @OA\Property(property="name", type="string", nullable=true, example="Новое название"),
 *     @OA\Property(property="data", type="object", nullable=true),
 *     @OA\Property(property="color", type="string", nullable=true, example="#0000ff")
 * )
 *
 * @OA\Schema(
 *     schema="AttachStickerRequest",
 *     required={"stickerId"},
 *     @OA\Property(property="stickerId", type="integer", example=2)
 * )
 *
 * @OA\Schema(
 *     schema="TimeInterval",
 *     required={"id", "taskId", "userId", "startTime", "createdAt", "updatedAt"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="taskId", type="integer", example=42),
 *     @OA\Property(property="userId", type="integer", example=17),
 *     @OA\Property(property="startTime", type="string", format="date-time", example="2026-02-28 10:00:00"),
 *     @OA\Property(property="endTime", type="string", format="date-time", nullable=true, example="2026-02-28 12:30:00"),
 *     @OA\Property(property="duration", type="integer", nullable=true, example=9000),
 *     @OA\Property(property="comment", type="string", nullable=true, example="Работа над задачей"),
 *     @OA\Property(property="type", type="string", enum={"timer", "plan"}, example="timer"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2026-02-28 10:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2026-02-28 12:30:00")
 * )
 *
 * @OA\Schema(
 *     schema="StartTimerRequest",
 *     required={"taskId"},
 *     @OA\Property(property="taskId", type="integer", example=42),
 *     @OA\Property(property="comment", type="string", nullable=true, example="Начал работу")
 * )
 *
 * @OA\Schema(
 *     schema="StopTimerRequest",
 *     @OA\Property(property="intervalId", type="integer", nullable=true, example=5),
 *     @OA\Property(property="comment", type="string", nullable=true, example="Закончил работу")
 * )
 *
 * @OA\Schema(
 *     schema="LogIntervalRequest",
 *     required={"taskId", "startTime", "endTime"},
 *     @OA\Property(property="taskId", type="integer", example=42),
 *     @OA\Property(property="startTime", type="string", format="date-time", example="2026-02-28 10:00:00"),
 *     @OA\Property(property="endTime", type="string", format="date-time", example="2026-02-28 12:30:00"),
 *     @OA\Property(property="comment", type="string", nullable=true, example="Ручной ввод")
 * )
 *
 * @OA\Schema(
 *     schema="DailySummary",
 *     required={"id", "taskId", "userId", "date", "totalDuration", "updatedAt"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="taskId", type="integer", example=42),
 *     @OA\Property(property="userId", type="integer", example=17),
 *     @OA\Property(property="date", type="string", format="date", example="2026-02-28"),
 *     @OA\Property(property="totalDuration", type="integer", example=3600),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2026-02-28 23:59:59")
 * )
 *
 * @OA\Schema(
 *     schema="TaskTimeSummary",
 *     required={"taskId", "taskTitle", "blocks", "totalDuration"},
 *     @OA\Property(property="taskId", type="integer", example=42),
 *     @OA\Property(property="taskTitle", type="string", example="Разработка модуля"),
 *     @OA\Property(
 *         property="blocks",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/TimeBlock")
 *     ),
 *     @OA\Property(property="totalDuration", type="integer", example=7200)
 * )
 *
 * @OA\Schema(
 *     schema="TimeBlock",
 *     required={"start", "end", "duration"},
 *     @OA\Property(property="start", type="string", format="date-time", example="2026-02-28 10:00:00"),
 *     @OA\Property(property="end", type="string", format="date-time", example="2026-02-28 12:00:00"),
 *     @OA\Property(property="type", type="string", nullable=true, enum={"single", "merged", "overlap"}),
 *     @OA\Property(property="taskIds", type="array", items=@OA\Items(type="integer"), nullable=true),
 *     @OA\Property(property="duration", type="integer", example=7200)
 * )
 */
class TaskSchemas {}