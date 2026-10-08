# Ядро системы (core/)

> Раздел документации ядра `core/`. Здесь описаны слои, DTO, команды/запросы, события, уведомления, исключения, безопасность, value objects, репозитории и конфигурация ядра.

Ядро — это фундамент приложения: пользователи, роли, назначение ролей, уведомления, шина событий, обработка ошибок, безопасность. Модули (`modules/*`) подключаются к ядру через порты и события.

## Структура документов

| Документ | Содержание |
|---|---|
| [architecture.md](./architecture.md) | Слои, DDD, Clean Architecture, CQRS; как ядро взаимодействует с модулями |
| [dto.md](./dto.md) | Все DTO ядра: поля, типы, сериализация, примеры JSON |
| [commands-queries.md](./commands-queries.md) | CQRS-команды/запросы и хендлеры ядра (роли, пользователи, назначения) |
| [events.md](./events.md) | Шина событий `GlobalEventDispatcher`, доменные события ядра, подписка модулей |
| [notifications.md](./notifications.md) | `NotificationHub`, каналы (email/telegram/push), формат уведомлений |
| [exceptions.md](./exceptions.md) | Иерархия доменных исключений, карта HTTP-статусов, `JsonErrorHandler` |
| [security.md](./security.md) | `YiiIdentity`, scopes, типы субъектов, интеграция с OAuth |
| [value-objects.md](./value-objects.md) | Value Objects ядра: `IdRange`, `Email`, `Date`, ID-объекты и др. |
| [repositories.md](./repositories.md) | Порты репозиториев и их реализации, транзакции |
| [configuration.md](./configuration.md) | Конфигурация ядра: `config/*`, DI, `params`, миграции; что задаётся где |

## Ключевые понятия (кратко)

- **Слои:** `core/presentation` → `core/application` → `core/domain`; `core/infrastructure` реализует порты; `core/security` — идентификация.
- **CQRS:** запись — команды + хендлеры (`application/command`, `application/handler`); чтение — запросы + хендлеры (`application/query`, `application/handler`).
- **События:** доменные события диспатчатся через `IEventDispatcher`; глобальная шина — `GlobalEventDispatcher`; модули подписываются в своих `config/di.php`.
- **Ответы:** единые обёртки `item` / `items+_meta` / `message` / `error` (см. [dto.md](./dto.md#базовые-обёртки-ответов)).
- **Пользователи:** локальная таблица `users` синхронизируется с Passport (`SyncUserHandler`).

## Карта каталога ядра

```
core/
├── application/
│   ├── command/        # CQRS-команды (CreateRole, AssignUserRole, SyncUser …)
│   ├── dto/            # DTO ядра (Collection, Item, Error, Success, Role, User, …)
│   ├── handler/        # Хендлеры команд/запросов
│   ├── notification/   # NotificationHub и каналы (email/telegram/push/sms)
│   ├── port/           # Порты: репозитории, диспетчер событий, транзакции
│   └── query/          # CQRS-запросы (ListRole, GetUser, …)
├── domain/
│   ├── entity/         # Role, User, UserRole
│   ├── event/          # UserFirstLoginEvent
│   ├── exception/      # Иерархия доменных исключений (19 классов)
│   └── valueObject/    # Email, Date, IdRange, RoleId, UserId, …
├── infrastructure/
│   ├── event/          # GlobalEventDispatcher
│   ├── handler/        # JsonErrorHandler
│   ├── migrations/     # users, rbac, seed
│   ├── persistence/    # ActiveRecord-модели (UserAR, RoleAR, UserRoleAR)
│   └── repository/     # Db-реализации репозиториев и транзакций
├── presentation/
│   ├── controller/     # BaseController, RoleController, UserController, …
│   ├── request/        # Request-модели (RoleRequest, UserRoleAssignRequest)
│   ├── swagger/        # Аннотации OpenAPI
│   └── view/           # Шаблоны (Swagger UI)
└── security/           # YiiIdentity
```

## Связь с модулями

| Модуль | Как использует ядро |
|---|---|
| `passport/auth` | `YiiIdentity` (по токену), `SyncUserHandler` (создание пользователя) |
| `rbac` | `YiiIdentity`, доменные исключения (`PermissionDeniedException`), `IAuthorizationService` |
| `projects` | Глобальное событие `UserFirstLoginEvent`, уведомления (`NotificationHub`), `IRoleRepository` |
| `tasks` | Порт `ITaskStatisticsService`, глобальный диспетчер событий, уведомления |
| `feedback` | `NotificationHub`, глобальный диспетчер событий, `IUserRepository` |