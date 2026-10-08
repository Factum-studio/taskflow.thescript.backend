# Модуль tasks (задачи, колонки, комментарии, стикеры, тайм-трекинг)

> Модуль отвечает за задачи, канбан-колонки, комментарии, стикеры, приоритеты и учёт времени. Работает в связке с модулем projects (задачи/колонки привязаны к доскам проектов).

## Документы модуля

| Документ | Содержание |
|---|---|
| [architecture.md](./architecture.md) | Слои, сущности, ключевые классы |
| [dto.md](./dto.md) | Все 10 DTO: поля, типы, примеры |
| [commands-queries.md](./commands-queries.md) | Команды и запросы (задачи, колонки, стикеры, таймеры) |
| [entities-events.md](./entities-events.md) | Сущности, события, листенеры, карта подписок |
| [time-tracking.md](./time-tracking.md) | Тайм-трекинг: таймеры, интервалы, daily summary, busy chart |
| [access.md](./access.md) | `ITaskAccess`, права на задачи и колонки |
| [configuration.md](./configuration.md) | DI, routing, события→листенеры |

## Краткая модель

```
Доска (проект) ── * Колонка (BoardColumn)
                    └── * Задача (Task)
                           ├── * Комментарий (Comment)
                           ├── * Стикер (Sticker)   [через task_sticker_map]
                           ├── * Интервал времени (TimeInterval)
                           └── * Сводка (DailySummary)
Приоритет (TaskPriority) ── * Задача
```

- **Task** — центральная сущность: title, описание, колонка, приоритет, дедлайн, planned-поля, исполнитель, доска, родительская задача, мягкое удаление (deleted_at), флаг complete/overdue.
- **BoardColumn** — колонка канбана: системное имя, label, сортировка, active/final, цвет.
- **Comment** — комментарии к задачам.
- **Sticker** — стикеры/теги (типы: user/project?...), привязка к задачам многие-ко-многим.
- **TimeInterval** — интервалы времени (типы: timer, manual, plan).
- **DailySummary** — дневная сводка по задаче/пользователю.

## Ключевые классы

| Класс | Роль |
|---|---|
| `TaskController`, `BoardColumnController`, `CommentController`, `StickerController`, `TaskStickerController`, `TimeIntervalController`, `PriorityController` | HTTP-эндпоинты |
| `CreateTaskHandler`, `UpdateTaskHandler`, `MoveTaskToColumnHandler`, `StartTimerHandler`, `StopTimerHandler` и др. | CQRS-хендлеры |
| `TaskAccess` | Права (делегирует в `IProjectAccess`) |
| `AutoTimerListener` | Автостарт/стоп таймера при переносе/назначении/удалении |
| `PlannedIntervalListener` | Синхронизация planned-интервалов с задачами |
| `TimeTrackingListener` | Обновление daily summary при остановке таймера |
| `TaskLoggerListener` | Логирование событий задач |
| `TaskNotificationListener` | Заготовка уведомлений (TODO) |
| `BusyChartService`, `TaskStatisticsService` | Аналитика времени и статистика |

## Взаимодействие

- **С проектами:** `TaskAccess` спрашивает `IProjectAccess` о правах на доску.
- **С ядром:** использует глобальный `IEventDispatcher` (через декоратор), порт ядра `ITaskStatisticsService`, исключения ядра.
- **Модуль самодостаточен** для хранения задач: таблицы `tasks`, `board_columns`, `comments`, `stickers`, `time_intervals`, `daily_summary` и др.

Детали — в документах модуля.