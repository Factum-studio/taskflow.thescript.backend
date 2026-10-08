# DTO модуля tasks

> Все DTO модуля: поля, типы, примеры JSON. DTO создаются ассемблерами из AR-моделей.

## 1. `TaskDto`

**Файл:** `modules/tasks/application/dto/TaskDto.php`

| Свойство | Тип | Описание |
|---|---|---|
| `id` | int | ID задачи |
| `title` | string | Название |
| `description` | ?string | Описание |
| `columnId` | int | Колонка |
| `priorityId` | int | Приоритет |
| `dueDate` | ?string | Дедлайн |
| `plannedStart` | ?string | Планируемое начало (`Y-m-d H:i:s`) |
| `plannedEnd` | ?string | Планируемый конец |
| `createdBy` | int | Создатель |
| `assignedTo` | ?int | Исполнитель |
| `boardId` | int | Доска |
| `parentId` | ?int | Родительская задача |
| `overdue` | bool | Просрочена |
| `createdAt` | string | Создана |
| `updatedAt` | string | Обновлена |
| `deletedAt` | ?string | Мягкое удаление |

```json
{
  "item": {
    "id": 100,
    "title": "Сверстать страницу",
    "description": "По макету",
    "columnId": 12,
    "priorityId": 2,
    "dueDate": "2026-04-01 18:00:00",
    "plannedStart": "2026-03-30 09:00:00",
    "plannedEnd": "2026-03-30 13:00:00",
    "createdBy": 12,
    "assignedTo": 7,
    "boardId": 5,
    "parentId": null,
    "overdue": false,
    "createdAt": "2026-03-17 10:00:00",
    "updatedAt": "2026-03-17 10:00:00",
    "deletedAt": null
  }
}
```

## 2. `CommentDto`

| Поле | Тип |
|---|---|
| `id` | int |
| `taskId` | int |
| `userId` | int |
| `content` | string |
| `createdAt` | string |
| `updatedAt` | string |

## 3. `StickerDto`

| Поле | Тип | Описание |
|---|---|---|
| `id` | int | |
| `name` | string | Имя |
| `type` | string | Тип |
| `projectId` | ?int | Проект (для user-стикеров) |
| `data` | ?array | JSON-данные |
| `color` | ?string | Цвет |
| `createdBy` | int | Создатель |
| `createdAt` / `updatedAt` | string | Даты |

## 4. `TimeIntervalDto`

| Поле | Тип | Описание |
|---|---|---|
| `id` | int | |
| `taskId` | int | |
| `userId` | int | |
| `startTime` | string `Y-m-d H:i:s` | Начало |
| `endTime` | ?string | Конец (null — активный) |
| `duration` | ?int | Секунды |
| `comment` | ?string | Комментарий |
| `createdAt` / `updatedAt` | string | Даты |

## 5. `DailySummaryDto`

| Поле | Тип | Описание |
|---|---|---|
| `id` | int | |
| `taskId` | int | |
| `userId` | int | |
| `date` | string `Y-m-d` | Дата |
| `totalDuration` | int | Секунды суммарно |
| `updatedAt` | string | |

## 6. `TaskTimeSummaryDto`

| Поле | Тип | Описание |
|---|---|---|
| `taskId` | int | |
| `taskTitle` | string | |
| `blocks` | TimeBlockDto[] | Блоки времени |
| `totalDuration` | int | Всего секунд |

## 7. `TimeBlockDto`

| Поле | Тип | Описание |
|---|---|---|
| `start` | string | Начало |
| `end` | string | Конец |
| `type` | ?string | `single`, `merged`, `overlap` |
| `taskIds` | ?array | Задачи (при пересечениях) |
| `duration` | ?int | Секунды |

## 8. `BusySegmentDto`

| Поле | Тип | Описание |
|---|---|---|
| `start` | string | `H:i` или `Y-m-d H:i:s` (в зависимости от детализации) |
| `end` | string | |
| `taskIds` | ?array | Пересекающиеся задачи |
| `anchorPoints` | ?array | Промежуточные якорные значения |

Создаётся `BusyChartService` (см. [time-tracking.md](./time-tracking.md)).

## 9. `TaskPriorityDto`

| Поле | Тип |
|---|---|
| `id` | int |
| `value` | int |
| `label` | string |
| `color` | ?string |

## 10. `BoardColumnDto`

| Поле | Тип | Описание |
|---|---|---|
| `id` | int | |
| `boardId` | int | |
| `name` | string | Системное имя |
| `label` | string | Отображаемое название |
| `sortOrder` | int | Порядок |
| `isActive` | bool | Активная |
| `isFinal` | bool | Финальная |
| `color` | ?string | |
| `workflowId` | ?int | |
| `createdAt` / `updatedAt` | string | Даты |

## 11. Ассемблеры

| Ассемблер | DTO |
|---|---|
| `TaskDtoAssembler` | TaskDto |
| `CommentDtoAssembler` | CommentDto |
| `StickerDtoAssembler` | StickerDto |
| `TimeIntervalDtoAssembler` | TimeIntervalDto |
| `DailySummaryDtoAssembler` | DailySummaryDto |
| `BoardColumnDtoAssembler` | BoardColumnDto |
| `TaskPriorityDtoAssembler` | TaskPriorityDto |
| `TaskTimeSummaryDtoAssembler` | TaskTimeSummaryDto |
| `TimeIntervalDtoAssembler` | TimeIntervalDto |