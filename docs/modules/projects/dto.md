# DTO модуля projects

> DTO модуля projects: `ProjectDto`, `BoardDto`, `ProjectUserDto`. Все создаются ассемблерами из AR-моделей/данных.

## 1. `ProjectDto`

**Файл:** `modules/projects/application/dto/ProjectDto.php`

Конструктор принимает массив `$data`. Публичные свойства:

| Свойство | Тип | Источник в data |
|---|---|---|
| `id` | int | `id` |
| `name` | string | `name` |
| `type` | string | `type` |
| `ownerId` | int | `ownerId` |
| `settings` | array | `settings` (default `[]`) |
| `createdAt` | string | `createdAt` |
| `updatedAt` | string | `updatedAt` |

Пример JSON (внутри `item`):

```json
{
  "item": {
    "id": 3,
    "name": "Проект клиента",
    "type": "agency",
    "ownerId": 12,
    "settings": {},
    "createdAt": "2026-03-17 17:42:00",
    "updatedAt": "2026-03-17 17:42:00"
  }
}
```

## 2. `BoardDto`

**Файл:** `modules/projects/application/dto/BoardDto.php`

| Свойство | Тип | Источник |
|---|---|---|
| `id` | int | `id` |
| `projectId` | int | `projectId` |
| `name` | string | `name` |
| `description` | ?string | `description` (default null) |
| `createdBy` | int | `createdBy` |
| `settings` | array | `settings` (default `[]`) |
| `createdAt` | string | `createdAt` |
| `updatedAt` | string | `updatedAt` |

```json
{
  "item": {
    "id": 5,
    "projectId": 3,
    "name": "Разработка",
    "description": "Доска разработки",
    "createdBy": 12,
    "settings": {},
    "createdAt": "2026-03-17 17:50:00",
    "updatedAt": "2026-03-17 17:50:00"
  }
}
```

## 3. `ProjectUserDto`

**Файл:** `modules/projects/application/dto/ProjectUserDto.php`

| Свойство | Тип | Источник |
|---|---|---|
| `projectId` | int | `projectId` |
| `userId` | int | `userId` |
| `role` | string | `role` (owner/admin/member) |
| `invitedBy` | ?int | `invitedBy` (default null) |
| `invitedAt` | ?string | `invitedAt` (default null) |
| `acceptedAt` | ?string | `acceptedAt` (default null) |
| `joinedAt` | string | `joinedAt` |

```json
{
  "item": {
    "projectId": 3,
    "userId": 12,
    "role": "owner",
    "invitedBy": null,
    "invitedAt": null,
    "acceptedAt": null,
    "joinedAt": "2026-03-17 17:42:00"
  }
}
```

## 4. Ассемблеры

| Ассемблер | Создаёт |
|---|---|
| `ProjectDtoAssembler` | `ProjectDto` из `ProjectAR`/данных |
| `BoardDtoAssembler` | `BoardDto` из `BoardAR`/данных |
| `ProjectUserDtoAssembler` | `ProjectUserDto` из `ProjectUserAR`/данных |

Ассемблеры регистрируются в DI (`modules/projects/config/di.php`) и вызываются хендлерами для формирования ответов.