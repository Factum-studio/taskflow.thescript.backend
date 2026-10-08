# Исключения ядра и обработка ошибок

> Документ описывает иерархию доменных исключений, карту HTTP-статусов, работу `JsonErrorHandler` и форматы ошибок API.

## 1. Иерархия исключений

Все доменные исключения находятся в `core/domain/exception/`.

```mermaid
graph TD
    DomainException --> BadRequestException
    DomainException --> ValidationException
    DomainException --> LogicException
    DomainException --> RuntimeException

    BadRequestException --> PermissionDeniedException
    BadRequestException --> EntityNotFoundException
    BadRequestException --> EntityAlreadyExistsException
    BadRequestException --> EntityUnavailableException
    BadRequestException --> InvalidCredentialsException
    BadRequestException --> EmailNotVerifiedException
    BadRequestException --> UserNotFoundException
    BadRequestException --> UserAlreadyExistsException
    BadRequestException --> IdentityNotFoundException
    BadRequestException --> IdentityAlreadyExistsException
    BadRequestException --> FileStorageException
    BadRequestException --> GoneException
    BadRequestException --> NotImplementedException

    IHttpException ~~~ BadRequestException  .- интерфейс с getStatusCode()
```

### Базовые классы

| Класс | Назначение |
|---|---|
| `DomainException` | Корень доменных исключений |
| `BadRequestException` | Ошибка запроса (клиентская ошибка) |
| `ValidationException` | Ошибка валидации (поля/данные) |
| `LogicException` | Нарушение бизнес-логики |
| `RuntimeException` | Внутренняя ошибка (техническая) |

### Интерфейс `IHttpException`

```php
interface IHttpException
{
    public function getStatusCode(): int;
}
```

Реализуется для маппинга на HTTP-статус (через `JsonErrorHandler`).

## 2. Карта HTTP-статусов

| HTTP | Исключение/ситуация |
|---|---|
| **200** | Успех |
| **302** | Редирект (SSO login/callback) |
| **400** | `BadRequestException`, `InvalidCredentialsException`, `EmailNotVerifiedException`, `NotImplementedException` |
| **401** | `UnauthorizedHttpException` (Yii), нет/просрочен токен |
| **403** | `PermissionDeniedException`, `IdentityNotFoundException` |
| **404** | `EntityNotFoundException`, `UserNotFoundException`, `IdentityNotFoundException` |
| **409** | `EntityAlreadyExistsException`, `UserAlreadyExistsException`, `IdentityAlreadyExistsException` |
| **410** | `GoneException` |
| **422** | `ValidationException` |
| **500** | `LogicException`, `RuntimeException`, `FileStorageException`, `EntityUnavailableException`, иные необработанные исключения |

> **Важно:** часть контроллеров не бросает исключения, а возвращает `ErrorDto` с кодом 200 (см. [known-issues-and-roadmap](../known-issues-and-roadmap.md#43-смешение-стилей-ответов-ошибочных-статусов)). Фронтенду нужно проверять структуру `error` даже при HTTP 200.

## 3. `JsonErrorHandler` (формат ошибки)

**Файл:** `core/infrastructure/handler/JsonErrorHandler.php`

Переопределяет `ErrorHandler` Yii2 — все ошибки возвращаются единообразно в JSON.

### Логика обработки

1. Определяется `statusCode`:
   - `HttpException` → `$exception->statusCode`
   - `IHttpException` → `$exception->getStatusCode()`
   - иначе → 500
2. Формируется `ErrorDto` (см. [dto.md](./dto.md#13-errordt-o)).
3. Если есть `getErrors()` или `getDetails()` — добавляются в `error.details`.
4. В dev-режиме (`YII_DEBUG === true`) добавляются отладочные данные:
   - `DEBUG_LVL >= 1` → `trace` (массив `{ file, line, function, class, type, args? }`)
   - `DEBUG_LVL >= 2` → `request` (`{ method, url, headers, body }` с редакцией секретов)
   - `DEBUG_LVL = 3` → в trace добавляются `args` с редукцией (объекты → "Object(ClassName)", массивы → "Array(N)", строки → "[REDACTED]")
5. Ошибка логируется через `Yii::error`.
6. Ответ отправляется.

### Redaction (редакция секретных данных)

| Что редакцируется | Где |
|---|---|
| `Authorization`, `Cookie`, `Set-Cookie` | Заголовки запроса (`details.request.headers`) |
| `password`, `oldPassword`, `newPassword`, `client_secret`, `refresh_token`, `access_token` | Тело запроса (`details.request.body`), рекурсивно по JSON |
| Строковые аргументы в trace (при LEVEL 3) | `[REDACTED]` |

### Пример dev-ответа с ошибкой

```json
{
  "error": {
    "code": 500,
    "message": "Undefined variable 'x'",
    "details": {
      "trace": [
        {
          "file": "/path/Handler.php",
          "line": 42,
          "function": "handle",
          "class": "SomeHandler",
          "type": "->"
        }
      ],
      "request": {
        "method": "POST",
        "url": "/api/task",
        "headers": {
          "content-type": ["application/json"],
          "authorization": "[REDACTED]"
        },
        "body": "{\"title\":\"...\",\"password\":\"[REDACTED]\"}"
      }
    }
  }
}
```

## 4. DEBUG_LVL уровни (переменная окружения)

| Значение | Что добавляется |
|---|---|
| 0 (по умолч.) | Только ошибка (production) |
| 1 | + trace |
| 2 | + trace + request (с редукцией) |
| 3 | + trace с аргументами + request