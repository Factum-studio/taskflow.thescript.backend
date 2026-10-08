# Инструкция для фронтендеров

> Этот документ описывает, как фронтенд-приложение взаимодействует с API TASKFLOW CRM: аутентификация, форматы запросов и ответов, ошибки, пагинация, типовые сценарии. Swagger-документация доступна на работающем приложении по адресу `/docs/swagger`.

---

## 1. Базовые сведения

- **Формат:** RESTful JSON, pretty URLs (без `index.php`).
- **Контент-тип запроса:** `application/json`, для загрузки файлов — `multipart/form-data`.
- **Ответ:** всегда `application/json` (кроме SSO-редиректов и прокси logout).
- **Кодировка:** UTF-8.
- **Публичный URL API:** например `https://api.taskflow.thescript.agency`.

Ниже в примерах базовый URL опущен: `/project` означает `GET https://api.taskflow.thescript.agency/project`.

---

## 2. Аутентификация

Аутентификация — через внешний сервис **Passport** (SSO, OAuth2). Фронтенд сам не хранит пароли.

### 2.1. Начало входа (SSO redirect)

Клик по «Войти» на фронтенде (или прямое открытие):

```http
GET /auth/login
```

Ответ: HTML-страница с мета-редиректом (через ~3 секунды) на `https://passport.../auth/authorize?...`. После входа на Passport пользователь возвращается на:

```http
GET /auth/callback?code=...&state=...
```

API меняет `code` на токены и выставляет **httpOnly-куки**:

| Кука | Назначение |
|---|---|
| `taskflow_access_token` | Access-токен (JWT RS256), TTL по умолчанию 3600 c |
| `taskflow_refresh_token` | Refresh-токен, TTL по умолчанию 2592000 c (~30 дней) |
| `taskflow_oauth_state` | Защита CSRF на время SSO-потока (10 минут) |

Затем редирект на `FRONTEND_URL` (куки выставляются на домен API).

> Фронтенд **не должен** читать эти куки из JS (они `httpOnly`). Достаточно браузера: куки автоматически отправляются с запросами к API.

### 2.2. Авторизация запросов

Два способа (поддерживаются оба):

1. **Куки (рекомендуется для браузерных SPA):** куки `taskflow_access_token` / `taskflow_refresh_token` уходят автоматически с каждым запросом (нужно настроить `credentials: 'include'` в fetch/axios).
2. **Заголовок (для мобильных/серверных клиентов):**

```http
Authorization: Bearer <access_token>
```

### 2.3. Обновление токена (refresh)

Если access-токен истёк — API **сам** попытается обновить его через refresh-токен из куки (или заголовка `Cookie`), выставит новые куки и продолжит обработку запроса. Клиент обычно **не получает 401** из-за истечения токена, если refresh-кука жива.

### 2.4. Выход

```http
GET /auth/logout
```

API проксирует запрос в Passport, удаляет куки токенов и возвращает результат Passport (статус и заголовки `Set-Cookie`).

### 2.5. Публичные маршруты

Не требуют аутентификации (и не блокируются RBAC):

- `GET /` (welcome)
- `GET /auth/*` (login, callback, logout)
- `GET /docs`, `GET /docs/*`, `GET /docs/swagger*`
- `OPTIONS *` (CORS preflight)
- (в dev) `GET /gii`, `GET /debug*`

---

## 3. Форматы ответов

### 3.1. Один объект — обёртка `item`

```json
{
  "item": {
    "id": 1,
    "name": "Проект клиента",
    "type": "agency",
    "settings": {}
  }
}
```

### 3.2. Список — обёртка `items` + `_meta`

```json
{
  "items": [
    { "id": 1, "name": "Проект A" },
    { "id": 2, "name": "Проект B" }
  ],
  "_meta": {
    "total": 2,
    "page": 1,
    "limit": 20,
    "pages": 1
  }
}
```

### 3.3. Успех без тела

```json
{ "message": "Project deleted" }
```

### 3.4. Ошибка — обёртка `error`

```json
{
  "error": {
    "code": 422,
    "message": "Validation failed",
    "details": {
      "errors": {
        "name": ["Необходимо заполнить «name»."]
      }
    }
  }
}
```

`details` появляется, только если есть; структура `details`:

- при ошибке валидации — `"errors": { поле: [сообщения] }`;
- при доменной ошибке — произвольные ключи (например, `Possible reasons`);
- в dev-режиме — `trace` и `request` (см. [core/exceptions.md](./core/exceptions.md)).

---

## 4. Пагинация

Все списковые эндпоинты (`…/index`, `…/list` и т.п.) поддерживают:

| Параметр | Тип | По умолчанию | Ограничения |
|---|---|---|---|
| `page` | int | 1 | ≥ 1 |
| `limit` | int | 20 | 1–1000 |

Пример: `GET /task?page=2&limit=50`.

Ответ содержит `_meta` (см. выше). Страниц: `ceil(total / limit)`.

---

## 5. Коды ответов

| Код | Значение |
|---|---|
| 200 | Успех (включая создание/обновление/удаление) |
| 302 | Редирект (SSO login/callback) |
| 400 | Некорректный запрос |
| 401 | Не аутентифицирован (нет/просрочен токен) |
| 403 | Доступ запрещён (RBAC / права внутри проекта) |
| 404 | Не найдено |
| 405 | Метод не поддерживается |
| 409 | Конфликт (например, сущность уже существует) |
| 410 | Gone (ресурс удалён) |
| 422 | Ошибка валидации |
| 500 | Внутренняя ошибка |

Точная карта «исключение → код» — в [core/exceptions.md](./core/exceptions.md).

---

## 6. CORS

API отвечает на `OPTIONS` (preflight) без аутентификации. Фронтенд на другом домене должен слать `OPTIONS`-запросы с нужными заголовками; API поддерживает это через маршрут `OPTIONS <any:.*>`.

---

## 7. Типовые сценарии (примеры запросов)

### 7.1. Список проектов пользователя

```http
GET /project
Cookie: taskflow_access_token=...
```

```json
{ "items": [ { "id": 3, "name": "Личный проект пользователя #12", "type": "personal", "settings": {} } ], "_meta": { "total": 1, "page": 1, "limit": 20, "pages": 1 } }
```

### 7.2. Создать проект

```http
POST /project
Content-Type: application/json

{ "name": "Проект клиента", "type": "agency", "settings": {} }
```

### 7.3. Доски проекта

```http
GET /project/3/board
GET /project/3/board       # после создания доски:
```

### 7.4. Колонки доски

```http
GET  /board/5/column
POST /board/5/column                # {"title": "В работе"}
PUT  /board/column/12               # {"title": "In Progress"}
POST /board/5/column/reorder        # {"columnIds": [12, 13, 11]}
DELETE /board/column/14
```

### 7.5. Задачи

```http
GET  /task?limit=50&page=1
POST /task                          # создать
PUT  /task/100                      # обновить
POST /task/100/move-to-column       # {"columnId": 12}
POST /task/100/assign               # {"assigneeId": 7}
POST /task/100/restore              # восстановить из удалённых
DELETE /task/100                    # мягкое удаление
DELETE /task/100/hard               # жёсткое удаление (только с правами)
```

### 7.6. Тайм-трекинг

```http
POST /time-interval/start           # {"taskId": 100}
POST /time-interval/stop            # {"taskId": 100}
POST /time-interval                 # ручной интервал: {"taskId":100,"startedAt":"...","endedAt":"...","duration":...}
GET  /time-interval?taskId=100
GET  /time-interval/daily-summary?date=2026-03-01
GET  /task/100/time-summary
```

### 7.7. Комментарии к задаче

```http
GET  /task/100/comment
POST /task/100/comment              # {"text": "..."}
PUT  /comment/55
DELETE /comment/55
```

### 7.8. Стикеры

```http
GET  /sticker
POST /sticker                       # создать стикер
GET  /task/100/sticker              # стикеры задачи
POST /task/100/sticker              # {"stickerId": 3}
DELETE /task/100/sticker/3
```

### 7.9. Участники и приглашения

```http
GET    /project/3/member
POST   /project/3/member            # {"userId": 7, "role": "member"}
PUT    /project/3/member/7/role     # {"role": "manager"}
DELETE /project/3/member/7
POST   /project/3/invite            # {"email": "...", "role": "member"}
POST   /invitation/accept           # {"token": "..."} (см. модуль projects)
DELETE /project/3/invitation
```

### 7.10. RBAC (роли/разрешения — обычно админка)

```http
GET    /roles
GET    /users
GET    /users/7
POST   /user-roles                  # {"userId": 7, "roleId": 2}
DELETE /user-roles/15
GET    /rbac/permissions
POST   /rbac/roles/2/permissions/5  # выдать разрешение роли
DELETE /rbac/roles/2/permissions/5
```

### 7.11. Обратная связь

```http
POST /feedback/rating               # {"speed":5,"functionality":4,"design":5,"usability":4}
POST /feedback/idea                 # {"type":"feature","title":"...","description":"..."}
GET  /feedback/idea
GET  /feedback/stats
```

---

## 8. Обработка ошибок на фронтенде

1. Проверять `response.ok` / HTTP-статус.
2. При `401` — если есть refresh-кука, повторить запрос один раз (API сам обновил токен, если это возможно); иначе показать «Войдите в систему» и редирект на `/auth/login`.
3. При `422` — показать поля из `error.details.errors` у соответствующих полей формы.
4. При `403` — показать сообщение «Недостаточно прав» (проверить, что пользователь действительно участник/владелец).
5. При `404` — «Не найдено» (ресурс удалён или нет доступа).
6. При `5xx` — общее сообщение, в dev можно показать `error.details.trace`.

Идемпотентность: POST-операции не идемпотентны (создают новые сущности); PUT/DELETE — идемпотентны.

---

## 9. Советы и ограничения

- Все даты в API — в формате ISO 8601 / Unix timestamp? — **проверяйте по Swagger** (модули используют разные DTO; точные типы полей см. в документации модулей).
- `limit` обрезается до диапазона 1–1000 принудительно.
- Для запросов с телом используйте `Content-Type: application/json`; `multipart/form-data` поддерживается парсером Yii (полезно для файлов в будущем).
- Значения «пустая строка» в multipart трактуются API как отсутствие значения (см. `loadMultipartModel` в `BaseController`).

---

## 10. Где взять полный контракт

- Swagger UI: `https://<api>/docs/swagger`, JSON: `https://<api>/docs/swagger/json`.
- Описание DTO модулей: [docs/modules/](./modules/) (passport, rbac, projects, tasks, feedback).
- Форматы ответов ядра: [docs/core/dto.md](./core/dto.md).
- Ошибки: [docs/core/exceptions.md](./core/exceptions.md).