# Конфигурация ядра

> Документ описывает, где хранятся конфигурации, что задаётся через `.env`, что через PHP-конфиги, а что зашито в коде. Понимание этого критично для деплоя и отладки.

---

## 1. Типы конфигурации

| Где | Пример | Можно менять без вмешательства в код? |
|---|---|---|
| `.env` | `DB_DSN`, `REDIS_HOST` | ✅ Да |
| `config/*.php` | `config/web.php`, `config/db.php` | ✅ Да (PHP-файлы) |
| `modules/*/config/params.php` | `modules/rbac/config/params.php` | ✅ Да (для модулей) |
| PHP-константы / аргументы конструктора | `pageSizeLimit = [1, 1000]` | ❌ Нет, в коде ядра/модулей |
| ENV-дефолты в params.php | `$_ENV['OAUTH2_ACCESS_TOKEN_TTL'] ?? 3600` | ✅ Частично (можно через .env) |

---

## 2. Переменные окружения (.env)

Файл `.env` (загружается `vlucas/phpdotenv`, не коммитится). Эталон — `.env.example`.

**Обязательные:**

| Ключ | Назначение | Где используется |
|---|---|---|
| `APP_NAME` | Имя приложения | config/web/console id |
| `APP_ENV` | `dev` / `prod` | условные блоки |
| `COOKIE_VALIDATION_KEY` | Валидация кук | web.php request.cookieValidationKey |
| `ADMIN_EMAIL` | Email администратора | config/params.php → adminEmail |
| `DB_DSN` | DSN БД | config/db.php |
| `DB_USERNAME` | Логин БД | config/db.php |
| `DB_PASSWORD` | Пароль БД | config/db.php |
| `REDIS_HOST` | Redis хост | container.php (Redis) |
| `REDIS_PORT` | Redis порт | container.php (Redis) |
| `PASSPORT_URL` | URL Passport | passport/auth/config/params.php |
| `FRONTEND_URL` | URL фронтенда | passport/auth/config/params.php |
| `APP_URL` | URL API | passport/auth/config/params.php |
| `OAUTH2_CLIENT_ID` | ID OAuth-клиента | passport DI |
| `OAUTH2_CLIENT_SECRET` | Секрет OAuth-клиента | passport DI |
| `OAUTH2_PUBLIC_KEY_PATH` | Путь к публичному ключу RS256 | passport DI |
| `MAILER_SCHEME/HOST/PORT/USERNAME/PASSWORD/ENCRYPTION` | Настройки SMTP | web.php mailer, console.php mailer |
| `SENDER_EMAIL / SENDER_NAME` | Отправитель писем | config/params.php, container.php |

**Опциональные (есть дефолт в params.php):**

| Ключ | Дефолт |
|---|---|
| `OAUTH2_ACCESS_TOKEN_TTL` | 3600 |
| `OAUTH2_REFRESH_TOKEN_TTL` | 2592000 |
| `DEBUG_LVL` | 0 |
| `REDIS_PASSWORD` | '' (пусто) |

---

## 3. Файлы `config/*.php`

### 3.1. `config/params.php`

Общие параметры (читаются из `.env`):

```php
return [
    'adminEmail'    => $_ENV['ADMIN_EMAIL'],
    'senderEmail'   => $_ENV['SENDER_EMAIL'],
    'senderName'    => $_ENV['SENDER_NAME'],
];
```

### 3.2. `config/db.php`

Параметры подключения к MySQL:

```php
return [
    'class'     => 'yii\db\Connection',
    'dsn'       => $_ENV['DB_DSN'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8',
];
```

### 3.3. `config/web.php`

Основная конфигурация веб-приложения. Содержит:

- **Пути к модулям** — перечисление `$diConfigs` (файлы modules/*/config/di.php).
- **Middleware** — `on beforeRequest` (PassportAuth + RBAC).
- **Компоненты:**
  - `request`: парсеры (JSON, multipart), cookieValidationKey.
  - `response`: JSON, UTF-8.
  - `cache`: `yii\caching\FileCache`.
  - `user`: identityClass = `YiiIdentity`, autoLogin/Session = false.
  - `errorHandler`: `JsonErrorHandler`.
  - `mailer`: SMTP-транспорт из .env.
  - `log`: fileTarget, уровни error/warning, exclude 404.
  - `db`: коннекшен из db.php.
  - `urlManager`: pretty URLs, rules (модули + ядро).
- **Параметры** (`params`).
- **Dev-блок:** при `YII_ENV_DEV` добавляет модули `debug` и `gii`.

**Что задаётся в коде (жёстко):**
- `defaultPageSize = 20`, `pageSizeLimit = [1, 1000]` (в `BaseController`).
- Charset таблиц: `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci` (в миграциях).
- `showScriptName = false`, `enablePrettyUrl = true`.

### 3.4. `config/console.php`

Конфигурация для консольных команд (`php yii ...`). Отличается от web.php:

- Нет beforeRequest middleware.
- Свои компоненты: Redis-кэш (вместо FileCache), нет модулей (только projects + tasks DI).
- controllerMap: `email-send`, `migrate` (с миграциями).

### 3.5. `config/modules.php`

Реестр модулей — список `['class', 'controllerNamespace']`:

```php
'auth' => ['class' => 'modules\\passport\\auth\\Module', 'controllerNamespace' => …],
'tasks', 'projects', 'rbac', 'feedback'
```

### 3.6. `config/container.php`

**DI-контейнер ядра.** Регистрирует:

- **Репозитории** (синглтоны):
  - `DbRoleRepository`, `DbUserRepository`, `DbUserRoleRepository`, `DbUserRoleSearch`, `DbTransactionManager`.
- **Хендлеры** (синглтоны): все хендлеры ролей, пользователей, user-role.
- **Redis** (новая инстанция при каждом `$container->get(Redis::class)`).
- **NotificationHub** + каналы (`PushChannel` → Redis, `EmailChannel` → SENDER_EMAIL/NAME, `TelegramChannel`).
- **IEventDispatcher** (синглтон `GlobalEventDispatcher`).

### 3.7. `config/migration_namespaces.php`

Пространства имён для миграций (используется при `php yii migrate`):

```php
'core\\infrastructure\\migrations',
'modules\\rbac\\infrastructure\\migrations',
'modules\\tasks\\infrastructure\\migrations',
'modules\\projects\\infrastructure\\migrations',
'modules\\feedback\\infrastructure\\migrations',
```

### 3.8. `config/test.php` и `config/test_db.php`

Конфигурации для тестов. `test_db.php` может переопределять db.

---

## 4. Что задаётся в коде (жёстко) и почему

| Файл | Что | Почему не вынесено в конфиг |
|---|---|---|
| `BaseController` | `pageSizeLimit = [1, 1000]`, `defaultPageSize = 20` | Константы приложения, разумные пределы |
| `SyncUserHandler` | Роль по умолчанию: сначала `'user'`, для owner ещё `'admin'` | Бизнес-логика (может быть сменено через разрешения, но не вынесено) |
| `EmailChannel` | Тип уведомления маппится на канал в `match (type)` | Switch по типу — это не конфигурация, а роутинг |
| `PassportAuthMiddleware` | Cookie-параметры: `httpOnly=true, secure=true, sameSite=LAX` | Оптимальные умолчания; `sameSite`, `httpOnly` и `secure` жёстко |
| `PassportAuthMiddleware` | Алгоритм JWT: RS256 | Задан Passport |
| `RbacMiddleware` | Правила `public_routes` и `permission_rules` | В params.php модуля rbac (это конфиг, но PHP-файл) |
| Миграции | charset `utf8mb4`, engine InnoDB | Стандартные оптимизации |
| `NotificationHub` | Каналы в конструкторе (email/telegram/push) | Новые каналы нужно добавить в код |

---

## 5. Миграции

| Файл | Таблицы |
|---|---|
| `core/migrations/m260922_080000_create_users_table.php` | `users` |
| `core/migrations/m260922_080001_create_rbac_tables.php` | `roles`, `user_roles` |
| `core/migrations/m260922_080002_seed_basic_data.php` | seed: роли `user`, `admin` |
| `modules/rbac/migrations/m260922_080010_create_permission_tables.php` | `permissions`, `role_permissions` |
| `modules/tasks/migrations/*` | `tasks`, `task_comments`, `task_time_intervals`, `task_priorities`, `task_statuses`, `board_columns`, `stickers`, `task_sticker_map`, `daily_time_summary` |
| `modules/projects/migrations/*` | `project`, `board`, `project_user`, `project_invitation` |
| `modules/feedback/migrations/*` | `feedback_rating`, `feedback_idea` |