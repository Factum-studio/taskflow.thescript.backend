# Поток обратной связи (flow) модуля feedback

> Документ описывает жизненный цикл оценки и идеи: от HTTP-запроса до уведомления.

## 1. Отправка оценки

```mermaid
sequenceDiagram
    autonumber
    participant U as Пользователь
    participant C as FeedbackController
    participant H as SubmitRatingHandler
    participant R as IFeedbackRatingRepository
    participant E as GlobalEventDispatcher (ядро)
    participant L as SendNonMaxRatingEmailListener
    participant N as NotificationHub

    U->>C: POST /feedback/rating {speed, functionality, design, usability}
    C->>H: SubmitRatingCommand
    H->>H: валидация Rating (0..5)
    H->>R: upsert по userId (find or create + updateRatings)
    H->>E: dispatch RatingSubmittedEvent (глобальная шина)
    E->>L: handle(RatingSubmittedEvent)
    L->>L: isMaxRating()? (все >= 4)
    alt оценка НЕ максимальная (есть < 4)
        L->>N: Notification(email, 'Помогите нам стать лучше', ...)
        N->>N: EmailChannel::send
    else оценка максимальная
        N-->>L: (письмо не отправляется)
    end
    H-->>C: FeedbackRatingDto
    C-->>U: {item: {...}}
```

## 2. Отправка идеи

```mermaid
sequenceDiagram
    autonumber
    participant U as Пользователь
    participant C as FeedbackController
    participant H as SubmitIdeaHandler
    participant R as IFeedbackIdeaRepository
    participant E as GlobalEventDispatcher

    U->>C: POST /feedback/idea {type, comment}
    C->>H: SubmitIdeaCommand
    H->>H: валидация IdeaType
    H->>R: save FeedbackIdea
    H->>E: dispatch IdeaSubmittedEvent (слушателей пока нет)
    H-->>C: FeedbackIdeaDto
    C-->>U: {item: {...}}
```

> `IdeaSubmittedEvent` диспатчится, но не имеет слушателей (каркас для будущих уведомлений/модерации).

## 3. Чтение идей и статистики

| Запрос | Путь | Результат |
|---|---|---|
| Список идей | `GET /feedback/idea` | `FeedbackIdeaDto[]` |
| Статистика | `GET /feedback/stats` | `FeedbackStatsDto` |

## 4. Ключевые правила

- **Одна оценка на пользователя**: `feedback_rating.user_id` — UNIQUE; повторная отправка обновляет (`updateRatings`).
- **Диапазон оценки**: 0–5 (`Rating` VO); нарушение → `InvalidArgumentException`.
- **«Максимальная оценка»**: `isMaxRating()` = все 4 критерия ≥ 4. Письмо уходит только при оценке 0–3 хотя бы по одному критерию.
- **Типы идей**: `idea`, `bug`, `feature`, `improvement`, `question`.

## 5. Таблицы

| Таблица | Поля (основные) |
|---|---|
| `feedback_rating` | `id`, `user_id` (UNIQUE, FK→users), `speed`, `functionality`, `design`, `usability` (CHECK 0–5), даты |
| `feedback_idea` | `id`, `user_id`, `type`, `comment`, `is_implemented`, даты |