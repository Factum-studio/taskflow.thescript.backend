# DTO модуля feedback

> DTO модуля обратной связи: `FeedbackRatingDto`, `FeedbackIdeaDto`, `FeedbackStatsDto`. Все реализуют `JsonSerializable`.

## 1. `FeedbackRatingDto`

**Файл:** `modules/feedback/application/dto/FeedbackRatingDto.php`

| Поле | Тип | Описание |
|---|---|---|
| `userId` | int | ID пользователя |
| `speed` | int | Оценка скорости (0–5) |
| `functionality` | int | Оценка функциональности |
| `design` | int | Оценка дизайна |
| `usability` | int | Оценка удобства |
| `createdAt` | string `Y-m-d H:i:s` | |
| `updatedAt` | string `Y-m-d H:i:s` | |

Фабрика: `fromEntity(FeedbackRating)`.

```json
{
  "item": {
    "userId": 12,
    "speed": 5,
    "functionality": 4,
    "design": 5,
    "usability": 4,
    "createdAt": "2026-10-07 08:00:00",
    "updatedAt": "2026-10-07 08:00:00"
  }
}
```

## 2. `FeedbackIdeaDto`

**Файл:** `modules/feedback/application/dto/FeedbackIdeaDto.php`

| Поле | Тип | Описание |
|---|---|---|
| `id` | int | ID идеи |
| `userId` | int | Автор |
| `type` | string | `idea` / `bug` / `feature` / `improvement` / `question` |
| `comment` | string | Текст |
| `isImplemented` | bool | Реализована ли |
| `createdAt` / `updatedAt` | string | Даты |

## 3. `FeedbackStatsDto`

**Файл:** `modules/feedback/application/dto/FeedbackStatsDto.php`

| Поле | Тип | Описание |
|---|---|---|
| `totalIdeas` | int | Всего идей |
| `maxRatings` | int | Оценок «максимум» (все 4-5) |
| `implementedIdeas` | int | Реализованных идей |
| `minRatings` | int | Оценок «ниже максимума» |
| `totalUsers` | int | Всего пользователей (из ядра) |
| `totalTasks` | int | Всего задач (через ITaskStatisticsService) |

```json
{
  "item": {
    "totalIdeas": 12,
    "maxRatings": 34,
    "implementedIdeas": 3,
    "minRatings": 9,
    "totalUsers": 120,
    "totalTasks": 1540
  }
}
```