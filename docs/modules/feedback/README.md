# Модуль feedback (обратная связь)

> Модуль отвечает за обратную связь пользователей: оценки сервиса (по 4 критериям) и идеи по улучшению. При не максимальной оценке отправляется письмо через `NotificationHub` ядра.

## Документы модуля

| Документ | Содержание |
|---|---|
| [architecture.md](./architecture.md) | Слои, сущности, key classes |
| [dto.md](./dto.md) | `FeedbackRatingDto`, `FeedbackIdeaDto`, `FeedbackStatsDto` |
| [flow.md](./flow.md) | Поток: отправка оценки/идеи → события → уведомления |
| [configuration.md](./configuration.md) | DI, routing, подписки |

## Краткая модель

```mermaid
graph LR
    U[Пользователь] -->|POST /feedback/rating| R[FeedbackRating]
    U -->|POST /feedback/idea| I[FeedbackIdea]
    R -->|RatingSubmittedEvent| N[SendNonMaxRatingEmailListener]
    N --> H[NotificationHub (ядро)]
    H -->|email| EM[Email]
```

- **FeedbackRating** — оценка по 4 критериям: `speed`, `functionality`, `design`, `usability` (0–5). Один пользователь — одна оценка (upsert).
- **FeedbackIdea** — идея/баг/фича/улучшение/вопрос с типом и комментарием, флаг `isImplemented`.

## Ключевые классы

| Класс | Роль |
|---|---|
| `FeedbackController` | Эндпоинты: submit-rating, submit-idea, ideas, stats |
| `SubmitRatingHandler` / `SubmitIdeaHandler` | CQRS-хендлеры |
| `SendNonMaxRatingEmailListener` | Письмо при не максимальной оценке |
| `BaseNotificationListener` | Базовый класс рассылки (email/push) |
| `GetFeedbackStatsHandler` | Статистика (идеи, оценки, пользователи, задачи) |

## Взаимодействие

- **С ядром:** `IUserRepository` (email пользователя), `NotificationHub`/`Notification` (отправка), `ITaskStatisticsService` (кол-во задач в stats).
- **События:** `RatingSubmittedEvent` слушается глобально через `GlobalEventDispatcher` (подписка в `config/di.php` модуля); `IdeaSubmittedEvent` — без слушателей (каркас).

Детали — в документах модуля.