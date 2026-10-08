# DTO ядра (core/application/dto)

> Все DTO ядра: назначение, конструктор, поля, сериализация, примеры JSON.

## 1. Базовые обёртки ответов

Эти DTO используются **всеми** контроллерами API (ядро и модули) через `BaseController`.

### 1.1. `ItemDto` — один объект

`item($dto)` в контроллере оборачивает объект в `{ "item": ... }`.

```json
{
  "item": { "id": 1, "name": "user" }
}
```

Конструктор: `new ItemDto(mixed $item)`.
Сериализация: `['item' => $this->item]`.

### 1.2. `CollectionDto` — список с мета-данными пагинации

`collection(array $items, ?int $total, ?int $page, ?int $limit)`.

```json
{
  "items": [ { "id": 1 }, { "id": 2 } ],
  "_meta": {
    "total": 2,
    "page": 1,
    "limit": 20,
    "pages": 1
  }
}
```

Конструктор: `new CollectionDto(array $items, ?int $total = null, ?int $page = null, ?int $limit = null)`.

Сериализация:
- `items` — всегда;
- `_meta` — только если `total !== null`: `{ total, page, limit, pages }`, где `pages = ceil(total / limit)` (при `limit > 0`).

### 1.3. `ErrorDto` — ошибка

`error(string $message, int $code = 400, array $details = [])`.

```json
{
  "error": {
    "code": 422,
    "message": "Validation failed",
    "details": { "errors": { "name": ["Необходимо заполнить «name»."] } }
  }
}
```

Сериализация: `{ error: { code, message } }`; `details` добавляется, только если не пуст. `details` — произвольные ключи (например, `errors` от валидации, `trace`, `request` от `JsonErrorHandler`).

### 1.4. `SuccessDto` — успех без объекта

`success($data = null, string $message = 'OK')`.

Сериализация:
- `data === null` → `{ "message": "OK" }`;
- `data` массив → сам массив (без обёртки);
- `data` объект с `jsonSerialize` → результат его сериализации;
- иначе → `{ "data": ... }`.

Примеры:

```json
{ "message": "Project deleted" }
```

```json
{ "id": 1, "name": "user", "message": "OK" }   // если data = массив
```

> ⚠️ Обратите внимание: при передаче массива в `SuccessDto` обёртка не создаётся — фронтенд может получить «плоский» объект.

---

## 2. DTO предметной области

### 2.1. `RoleDto` — роль

Поля (сериализация):

| Поле | Тип | Описание |
|---|---|---|
| `id` | int | ID роли |
| `name` | string | Название (3–32 символа) |
| `description` | string \| null | Описание |
| `created_at` | string `Y-m-d H:i:s` | Дата создания |

Фабрика: `RoleDto::fromEntity(Role $role)`.

```json
{
  "item": {
    "id": 1,
    "name": "user",
    "description": "Зарегистрированный пользователь",
    "created_at": "2026-09-22 08:00:02"
  }
}
```

### 2.2. `UserDto` — пользователь

| Поле | Тип | Описание |
|---|---|---|
| `id` | int | Локальный ID |
| `passport_id` | int | ID в Passport (внешний) |
| `email` | string | Email |
| `surname` | string \| null | Фамилия |
| `name` | string \| null | Имя |
| `patronymic` | string \| null | Отчество |
| `post` | string \| null | Должность (из Passport) |
| `is_owner` | bool | Признак владельца (owner в Passport) |
| `synced_at` | string \| null `Y-m-d` | Дата последней синхронизации |
| `created_at` | string `Y-m-d H:i:s` | Создан |
| `updated_at` | string `Y-m-d H:i:s` | Обновлён |
| `roles` | UserRoleDto[] | Назначенные роли |

Фабрика: `UserDto::fromEntity(User $user)`.

```json
{
  "item": {
    "id": 12,
    "passport_id": 100500,
    "email": "user@example.com",
    "surname": "Иванов",
    "name": "Иван",
    "patronymic": "Иванович",
    "post": "Разработчик",
    "is_owner": false,
    "synced_at": "2026-10-01",
    "created_at": "2026-10-01 10:00:00",
    "updated_at": "2026-10-01 10:00:00",
    "roles": [
      { "id": 3, "user_id": 12, "role_id": 1, "assigned_at": "2026-10-01 10:00:00", "assigned_by": null }
    ]
  }
}
```

### 2.3. `UserRoleDto` — назначение роли

| Поле | Тип | Описание |
|---|---|---|
| `id` | int | ID назначения |
| `user_id` | int | ID пользователя |
| `role_id` | int | ID роли |
| `assigned_at` | string `Y-m-d H:i:s` | Когда назначено |
| `assigned_by` | int \| null | Кем назначено (ID пользователя) |

### 2.4. Фильтры для списков

Эти DTO не сериализуются в ответ — используются как параметры запросов (передаются в query-хендлеры):

| DTO | Поля (все nullable) |
|---|---|
| `RoleFiltersDto` | `ids` (строка диапазона, см. `IdRange`), `limit`, `offset`, `orderBy`, `name`, `description`, `createdFrom`, `createdTo` |
| `UserFiltersDto` | `ids`, `limit`, `offset`, `orderBy`, `email`, `surname`, `name`, `patronymic`, `isOwner`, `passportId`, `post`, `syncFrom`, `syncTo`, `createdFrom`, `createdTo`, `updatedFrom`, `updatedTo` |
| `UserRoleFiltersDto` | `ids`, `limit`, `offset`, `orderBy`, `userId`, `roleId`, `assignedFrom`, `assignedTo`, `assignedBy` |

Формат `ids` — строка в формате `IdRange` (см. [value-objects.md](./value-objects.md#idrange)): `1`, `1,2,3`, `2:20`, `1,3:20`.

---

## 3. Правила использования

1. Контроллеры НЕ возвращают сущности напрямую — только DTO (через хендлеры и ассемблеры).
2. Хендлеры возвращают DTO (или null → контроллер решает, как ответить).
3. Модули используют базовые обёртки ядра для единообразия ответов.
4. DTO предметной области как правило имеют фабрику `fromEntity()`.
5. Фильтры формируются на основе query-параметров и передаются в репозитории через Search-интерфейсы.

## 4. DTO модулей

DTO модулей описаны в документации каждого модуля:

- [projects/dto.md](../modules/projects/dto.md): `ProjectDto`, `BoardDto`, `ProjectUserDto`
- [tasks/dto.md](../modules/tasks/dto.md): `TaskDto`, `CommentDto`, `StickerDto`, `TimeIntervalDto`, `DailySummaryDto`, `TaskTimeSummaryDto`, `BusySegmentDto`, `TimeBlockDto`, `TaskPriorityDto`, `BoardColumnDto`
- [feedback/dto.md](../modules/feedback/dto.md): `FeedbackRatingDto`, `FeedbackIdeaDto`, `FeedbackStatsDto`
- [passport/dto.md](../modules/passport/dto.md): `PassportTokenDto`, `PassportUserDto`