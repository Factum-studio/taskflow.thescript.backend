# Инструкция для разработчиков (Backend)

> Этот документ описывает, как настроить окружение, запустить проект, писать код в соответствии с архитектурой проекта, добавлять модули, команды, события и тестировать изменения.

---

## Стек технологий

| Технология | Версия | Назначение |
|---|---|---|
| PHP | 8.1+ | Язык |
| Yii2 | 2.0.45+ | Фреймворк |
| MySQL | 8.0 | Основная БД |
| Redis | 7.0 | Кэш, push-уведомления |
| Passport | — | Внешний SSO-сервис (OAuth2) |
| Swagger-PHP | 5.8 | Генерация OpenAPI-документации |
| PHPStan | 2.2 | Статический анализ |
| PHP-CS-Fixer | 3.95 | Стиль кода |
| Codeception | 4/5 | Тесты |
| vlucas/phpdotenv | 5.6 | Переменные окружения |

---

## 1. Локальный запуск

### 1.1. Требования
- PHP 8.1+ с расширениями: `pdo_mysql`, `mbstring`, `intl`, `zip`, `openssl`, `redis`
- MySQL 8.0
- Redis 7.0
- Composer 2.x
- Доступ к Passport (для полной аутентификации) и публичный ключ OAuth2

### 1.2. Установка

```bash
# 1. Клонировать репозиторий
git clone <url-репозитория> taskflow.api
cd taskflow.api

# 2. Установить зависимости
composer install

# 3. Создать .env из примера
cp .env.example .env
# Отредактировать .env: БД, Redis, MAILER, OAUTH2_*
#  - OAUTH2_PUBLIC_KEY_PATH — путь к публичному ключу Passport (RS256)
#  - OAUTH2_CLIENT_ID / OAUTH2_CLIENT_SECRET — регистрация клиента в Passport

# 4. Применить миграции
php yii migrate --interactive=0

# 5. Запустить dev-сервер
php yii serve --docroot=web
```

Приложение доступно на `http://localhost:8080`.

### 1.3. Docker / Vagrant
- `docker-compose.yml` — каркас контейнеризации (нужно донастроить под текущий стек).
- `Vagrantfile` + `vagrant/provision/` — альтернативный способ локального окружения.

---

## 2. Переменные окружения (.env)

Все секреты и настройки окружения хранятся в `.env` (не в коде). Обязательные переменные — см. [core/configuration.md](./core/configuration.md#переменные-окружения-env).

Файл `.env.example` — эталон; `.env` — актуальные значения и он **не должен** попадать в git (см. `.gitignore`).

> ⚠️ Никогда не коммить `.env` с реальными секретами.

---

## 3. Команды консоли

Проект использует `./yii` (Linux) / `yii.bat` (Windows).

```bash
php yii migrate                   # Применить миграции
php yii migrate --interactive=0   # Без подтверждения
php yii email-send                # Отправка писем (очередь; см. commands/EmailSendCommand.php)
php yii cache/flush-all           # Полный сброс кэша
php yii serve --docroot=web       # Dev-сервер
```

Полезные Composer-скрипты:

```bash
composer test                      # Все тесты
composer test:unit                 # Юнит-тесты
composer test:api                  # API-тесты
composer lint                      # Проверка стиля (dry-run)
composer lint-fix                  # Авто-исправление стиля
composer unclestan:6               # PHPStan level 6 по core/ и modules/
```

---

## 4. Архитектура: как писать код

### 4.1. Слои
Строго соблюдаем слои (подробно: [core/architecture.md](./core/architecture.md)):

```
presentation/  →  application/  →  domain/
                        ↑               ↑
              infrastructure реализует порты (application/port/* и domain/repository/*)
```

- **presentation** — контроллеры, request-модели, middleware, представления. Не содержит бизнес-логики.
- **application** — команды/запросы (CQRS), хендлеры, DTO, порты, ассемблеры, сервисы.
- **domain** — сущности, value objects, доменные события, исключения, интерфейсы репозиториев.
- **infrastructure** — репозитории (ActiveRecord), миграции, HTTP-клиенты, слушатели событий, access-сервисы.

### 4.2. CQRS-шаблон добавления новой операции
Пример добавления операции «создать что-то»:

1. **Command** в `application/command/` — `CreateXxxCommand` (immutable, поля через конструктор).
2. **Handler** в `application/handler/` — `CreateXxxHandler` с методом `handle(CreateXxxCommand $command): XxxDto`.
3. **Query** (если чтение) в `application/query/` — `GetXxxQuery`, хендлер `GetXxxHandler`.
4. **DTO** в `application/dto/` — выходной объект с полями, реализует `\JsonSerializable` при необходимости.
5. **Controller** в `presentation/controller/` — действие вызывает `$this->container->get(Handler::class)->handle(...)`.
6. **Request**-модель (валидация входных данных) — в `presentation/request/`.
7. Зарегистрировать хендлер в DI (см. п. 4.3) и маршрут в `config/routing.php` модуля.

### 4.3. Регистрация зависимостей (DI)
- Ядро: `config/container.php` (репозитории ядра, хендлеры, уведомления, события).
- Модуль: `modules/<module>/config/di.php` (свои репозитории, слушатели, ассемблеры).
- Файлы подключаются автоматически: `config/web.php` перечисляет `$diConfigs`, `config/console.php` — тоже.

```php
// config/di.php модуля
Yii::$container->set(IXxxRepository::class, function () {
    return new DbXxxRepository(Yii::$app->db);
});
Yii::$container->set(CreateXxxHandler::class);
```

### 4.4. События и слушатели
Доменные события — основной способ реакции на изменения (уведомления, логирование, авто-действия).

1. Создать событие в `domain/event/` — класс с геттерами (immutable).
2. В коде хендлера вызвать диспетчер: `$this->eventDispatcher->dispatch(new XxxEvent(...))`.
3. Подписать слушателя:
   - локально в модуле — в `config/di.php` в `ModuleEventDispatcher` или `DispatchingEventDecorator`;
   - глобально (для событий других модулей/ядра) — `$globalDispatcher->addListener(EventClass::class, Listener::class)`.

Примеры: [core/events.md](./core/events.md), [modules/tasks](./modules/tasks/entities-events.md), [modules/projects](./modules/projects/entities-events.md).

### 4.5. Миграции
- Миграции модуля лежат в `modules/<module>/infrastructure/migrations/`, ядра — в `core/infrastructure/migrations/`.
- Пространства имён регистрируются в `config/migration_namespaces.php`.

```bash
php yii migrate/create create_xxx_table --namespace=modules\\xxx\\infrastructure\\migrations
```

Именование: `m260224_132707_create_tasks_table.php` (год_месяц_день_время_суффикс).

### 4.6. Маршруты
- Маршруты модуля — в `modules/<module>/config/routing.php`, массив `'<method> <pattern>' => '<controller>/<action>'`.
- Файлы подключаются в `config/web.php` через `array_merge(require ...)` в `urlManager.rules`.
- Yii2 pretty URLs, `showScriptName=false`.

---

## 5. Тестирование

### 5.1. Структура тестов
- `tests/unit/` — юнит-тесты (модели, виджеты).
- `tests/functional/` — функциональные тесты.
- `tests/acceptance/` — приёмочные (через браузер/API).
- Конфигурация: `codeception.yml`, `tests/` suite-файлы.

### 5.2. Запуск

```bash
composer test          # все
composer test:unit     # только юнит
composer test:api      # только API
vendor/bin/codecept run unit tests/unit/models/SomeTest      # конкретный тест
```

### 5.3. Качество кода

```bash
composer lint-fix          # php-cs-fixer
composer unclestan:6       # phpstan level=6
```

Конфигурация: `.php-cs-fixer.dist.php`, `phpstan.neon` + `phpstan-bootstrap.php`.

---

## 6. CI/CD

GitHub Actions: `.github/workflows/ci_cd.yml`

- **push / PR в `dev`**: прогон `composer validate`, установка зависимостей, создание `.env` из примера, `composer test`.
- **push в `dev`**: деплой на dev-сервер по SSH/rsync с исключением `.env`, `.git`, `.github`, `tests`.
- После деплоя: `composer install --no-dev`, `php yii cache/flush-all`.

Деплой в продакшн — только через настроенные CI/CD пайплайны, вручную — запрещено.

---

## 7. Стандарты кода (кратко)

Подробно: [specification/code-style.md](./specification/code-style.md).

- PHP 8.1+: readonly-свойства, промоушен конструктора, `match`, str-функции.
- `declare(strict_types=1);` в каждом файле.
- Слои: бизнес-логика в domain/application, не в контроллерах.
- Именование: `CamelCase` для классов, `camelCase` для методов/свойств, `snake_case` для БД.
- Валидация входных данных — в `presentation/request/*` (Yii-модели), не в хендлерах.
- Ошибки: бросать доменные исключения (`core\domain\exception\*`), не `\Exception` напрямую (см. [core/exceptions.md](./core/exceptions.md)).
- Обращение к БД — только через интерфейсы репозиториев (`domain/repository/*`), не напрямую к AR в хендлерах.

---

## 8. Чек-лист добавления нового модуля

1. Создать `modules/<name>/` со слоями `application|domain|infrastructure|presentation`.
2. `Module.php` — класс модуля.
3. `config/di.php` — репозитории, хендлеры, слушатели.
4. `config/routing.php` — маршруты.
5. `config/params.php` — параметры модуля (если нужны).
6. Зарегистрировать модуль в `config/modules.php`, подключить `di.php` и `routing.php` в `config/web.php`.
7. Миграции в `infrastructure/migrations/` + namespace в `config/migration_namespaces.php`.
8. Swagger-аннотации на контроллерах (`#[OA\Get]`, `#[OA\Post]` и т.д.).
9. Тесты в `tests/`.
10. Документация модуля в `docs/modules/<name>/`.