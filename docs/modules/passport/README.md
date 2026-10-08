# Модуль passport/auth (SSO-аутентификация)

> Модуль отвечает за аутентификацию через внешний сервис **Passport** (OAuth2/SSO). Он обеспечивает: инициацию входа, обработку callback, выдачу токенов в httpOnly-куки, верификацию JWT на каждый запрос, автоматическое обновление токенов и выход (proxy на Passport).

## Документы модуля

| Документ | Содержание |
|---|---|
| [architecture.md](./architecture.md) | Слои модуля, ключевые классы, DI |
| [flow.md](./flow.md) | Полный SSO-поток: login → callback → refresh → logout |
| [dto.md](./dto.md) | `PassportTokenDto`, `PassportUserDto` |
| [security.md](./security.md) | Middleware: валидация JWT, куки, public_routes |
| [configuration.md](./configuration.md) | Параметры модуля, env, что захардкожено |

## Краткая суть

```
Пользователь → GET /auth/login → редирект на Passport
   → Passport авторизует → callback ?code=&state=
   → API обменивает code на токены (PassportHttpClient)
   → синхронизирует пользователя (SyncUserHandler ядра)
   → кладёт токены в httpOnly-куки
   → редирект на фронтенд

Каждый защищённый запрос:
   PassportAuthMiddleware: валидирует JWT (RS256) → устанавливает YiiIdentity
   при истечении: отрабатывает refresh автоматически
```

## Структура модуля

```
modules/passport/auth/
├── Module.php
├── application/
│   ├── command/        # InitiateSsoLoginCommand, HandleSsoCallbackCommand
│   ├── dto/            # PassportTokenDto, PassportUserDto
│   ├── handler/        # InitiateSsoLoginHandler, HandleSsoCallbackHandler
│   └── port/           # PassportAuthPort (порт)
├── config/
│   ├── di.php          # регистрация HTTP-клиента, обработчиков, middleware
│   ├── params.php      # URL-адреса, OAuth, куки, скоупы, public routes
│   └── routing.php     # /auth/login, /auth/callback, /auth/logout
├── infrastructure/
│   └── http/
│       └── PassportHttpClient.php   # HTTP-реализация порта
└── presentation/
    ├── controller/AuthController.php
    ├── middleware/PassportAuthMiddleware.php
    └── view/{layout,login}          # HTML-страницы редиректов
```

## Ключевые классы

| Класс | Роль |
|---|---|
| `AuthController` | HTTP-эндпоинты `/auth/login`, `/auth/callback`, `/auth/logout` |
| `PassportAuthMiddleware` | Проверка/валидация JWT на каждый запрос, refresh, установка `YiiIdentity` |
| `PassportAuthPort` | Порт (интерфейс интеграции) |
| `PassportHttpClient` | Реализация порта: authorization URL, token exchange, refresh, getUser, logout |
| `InitiateSsoLoginHandler` | Строит URL авторизации Passport по `state` |
| `HandleSsoCallbackHandler` | Обменивает code → токены, синхронизирует пользователя |

## Взаимодействие с ядром

- `HandleSsoCallbackHandler` использует `SyncUserHandler` (ядро) — создаёт/обновляет локального пользователя и назначает роли `user`/`admin`.
- `PassportAuthMiddleware` создаёт `YiiIdentity` (ядро) — используется RBAC-модулем.
- Все модульные `config/routing.php` подключаются в `config/web.php` в `urlManager.rules`, а `config/di.php` — в `$diConfigs`.

> Подробности: [flow.md](./flow.md), [security.md](./security.md), [configuration.md](./configuration.md).