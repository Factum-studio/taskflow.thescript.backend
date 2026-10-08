# Архитектура модуля feedback

> Документ описывает слои, сущности и ключевые классы модуля обратной связи.

## 1. Структура

```
modules/feedback/
├── Module.php
├── application/
│   ├── command/         # SubmitRatingCommand, SubmitIdeaCommand
│   ├── dto/             # FeedbackRatingDto, FeedbackIdeaDto, FeedbackStatsDto
│   ├── handler/         # SubmitRatingHandler, SubmitIdeaHandler, GetFeedbackStatsHandler
│   ├── port/            # IFeedbackRatingRepository, IFeedbackIdeaRepository
│   └── query/           # GetFeedbackStatsQuery
├── config/
│   ├── di.php           # репозитории + глобальная подписка RatingSubmittedEvent
│   └── routing.php      # /feedback/rating, /feedback/idea, /feedback/stats
├── domain/
│   ├── entity/          # FeedbackRating, FeedbackIdea
│   ├── event/           # RatingSubmittedEvent, IdeaSubmittedEvent
│   └── valueObject/     # Rating, IdeaType
├── infrastructure/
│   ├── listener/        # SendNonMaxRatingEmailListener, BaseNotificationListener
│   ├── migrations/      # feedback_rating, feedback_idea
│   ├── persistence/     # FeedbackRatingAR, FeedbackIdeaAR
│   └── repository/      # DbFeedbackRatingRepository, DbFeedbackIdeaRepository
└── presentation/
    └── controller/      # FeedbackController
```

## 2. Сущности

### 2.1. `FeedbackRating`
- Поля: `userId`, `speed: Rating`, `functionality: Rating`, `design: Rating`, `usability: Rating`, даты.
- Методы: `updateRatings()`, `isMaxRating()`.
- Инвариант: `Rating` — 0..5.

### 2.2. `FeedbackIdea`
- Поля: `userId`, `type: IdeaType`, `comment`, `isImplemented`, даты.
- Методы: `markImplemented()`.

## 3. Value objects

### `Rating`
```php
new Rating(int $value);  // 0..5 иначе InvalidArgumentException
value(): int
isMax(): bool            // value >= 4
```

> ⚠️ Обратите внимание: `isMax()` возвращает `true` при **value >= 4**, т.е. оценка 4 тоже считается «максимальной» для целей отправки письма (письмо уходит только при оценке 0–3 хотя бы по одному критерию). Поведение задано в коде.

### `IdeaType`
Разрешённые значения: `idea`, `bug`, `feature`, `improvement`, `question`.

## 4. События

| Событие | Когда | Данные |
|---|---|---|
| `RatingSubmittedEvent` | Отправлена/обновлена оценка | `FeedbackRating` |
| `IdeaSubmittedEvent` | Отправлена идея | `FeedbackIdea` |

## 5. Листенеры

### `SendNonMaxRatingEmailListener` (extends `BaseNotificationListener`)
- Событие: `RatingSubmittedEvent`.
- Если `!$rating->isMaxRating()`:
  - email = `IUserRepository::findById(UserId)->getEmail()`.
  - `Notification` (email) с темой «Помогите нам стать лучше» и текстом-просьбой о фидбеке.
  - `NotificationHub::send()`.

### `BaseNotificationListener` (абстрактный)
Шаблон рассылки:
- `getTargetUserId(event): ?int`
- `getSubject(event): string`
- `getBody(event): string`
- `getChannels(): array` (default `['email','push']`)
- `handle(event)` — для каждого канала: `getRecipient(userId, channel)`:
  - `email` → email пользователя;
  - `push` → `(string)userId`;
  - `telegram`/`sms` → null (нет реципиента).
- Логирует warning, если реципиент не найден.

## 6. Порт и репозитории

| Порт | Реализация |
|---|---|
| `IFeedbackRatingRepository` | `DbFeedbackRatingRepository` (upsert по userId) |
| `IFeedbackIdeaRepository` | `DbFeedbackIdeaRepository` |

## 7. Статистика (`GetFeedbackStatsHandler`)

`FeedbackStatsDto` содержит: `totalIdeas`, `maxRatings`, `implementedIdeas`, `minRatings`, `totalUsers`, `totalTasks` (последний — через `ITaskStatisticsService` ядра).