# Модуль projects (проекты, доски, участники, приглашения)

> Модуль отвечает за проекты, канбан-доски, участников и приглашения. Взаимодействует с модулем tasks (доски/колонки/задачи) и с ядром (события, уведомления, пользователи).

## Документы модуля

| Документ | Содержание |
|---|---|
| [architecture.md](./architecture.md) | Слои, сущности, ключевые классы |
| [dto.md](./dto.md) | `ProjectDto`, `BoardDto`, `ProjectUserDto` |
| [commands-queries.md](./commands-queries.md) | Все команды/запросы и хендлеры |
| [entities-events.md](./entities-events.md) | Сущности, события, листенеры |
| [access.md](./access.md) | `IProjectAccess`, `ProjectAccess`, права |
| [configuration.md](./configuration.md) | DI, routing, параметры |

## Краткая модель

```
Проект (Project) 1 ── * Доска (Board)
    │
    ├─ * Участник (ProjectUser role: owner/admin/member)
    └─ * Приглашение (Invitation: pending → accepted/cancelled)
```

- **Проект** — контейнер; типы: `personal`, `collaborative`, `corporate`, `agency` (см. `ProjectType`).
- **Доска** — канбан-доска внутри проекта (задачи модуля tasks привязываются к доске через `board_id`).
- **Участник** — пользователь с ролью внутри проекта.
- **Приглашение** — по email с токеном; принимается/отменяется.

## Ключевые классы

| Класс | Роль |
|---|---|
| `ProjectController`, `BoardController`, `ProjectMemberController`, `InvitationController` | HTTP-эндпоинты |
| `CreateProjectHandler`, `UpdateProjectHandler`, `DeleteProjectHandler`, … | CQRS-хендлеры |
| `ProjectAccess` | Централизованная проверка прав на проекты/доски |
| `ModuleEventDispatcher` + `DispatchingEventDecorator` | Шина событий модуля (с пробросом в глобальную) |
| `AddOwnerAsMemberListener` | Автодобавление владельца в участники при создании проекта |
| `SendInvitationEmailListener` | Отправка письма с приглашением |
| `CreatePersonalProjectOnUserFirstLogin` | Автосоздание личного проекта при первом входе (глобальное событие ядра) |
| `ProjectLoggerListener` | Логирование событий проекта |

## Взаимодействие

- **С ядром:** слушает глобальное `UserFirstLoginEvent`; использует `NotificationHub` и `IRoleRepository`/`IUserRepository`; бросает доменные исключения ядра.
- **С модулем tasks:** доски принадлежат проектам; `TaskAccess` спрашивает `IProjectAccess` о правах на доску.

Детали — в документах модуля.