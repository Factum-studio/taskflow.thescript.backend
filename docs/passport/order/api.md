# Локальная копия "API и команды модуля Orders"

> Полный справочник HTTP-эндпоинтов, прав доступа и внутренних команд/запросов модуля.

---

## Содержание

- [Общие принципы](#общие-принципы)
- [Маршруты (сводная таблица)](#маршруты-сводная-таблица)
- [POST /orders — создание заказа (intake)](#post-orders--создание-заказа-intake)
- [GET /orders — список заказов](#get-orders--список-заказов)
- [GET /orders/{id} — заказ](#get-ordersid--заказ)
- [GET /orders/{id}/events — события](#get-ordersidevents--события)
- [GET /orders/{id}/deliveries — доставки](#get-ordersiddeliveries--доставки)
- [POST /orders/{id}/status — смена статуса](#post-ordersidstatus--смена-статуса)
- [POST /orders/{id}/ack — подтверждение доставки](#post-ordersidack--подтверждение-доставки)
- [POST /order-inbox — входящий вебхук](#post-order-inbox--входящий-вебхук)
- [POST /orders/{id}/replay — пересоздание доставок](#post-ordersidreplay--пересоздание-доставок)
- [POST /orders/{id}/archive — архивация](#post-ordersidarchive--архивация)
- [Коды ответов и ошибки](#коды-ответов-и-ошибки)
- [Права доступа (OAuth scopes + RBAC permissions)](#права-доступа-oauth-scopes--rbac-permissions)
- [Внутренние команды и запросы (CQRS)](#внутренние-команды-и-запросы-cqrs)

---

## Общие принципы

- Все ответы обёрнуты в DTO: `ItemDto` (`{"item": ...}`), `CollectionDto` (`{"items": [...], "_meta": {...}}`), `SuccessDto` (`{"message": "..."}`).
- Ошибки — в формате `ErrorDto` (`{"error": {"code": ..., "message": ..., "details": ...}}`).
- Даты в ответах: `Y-m-d H:i:s` (кроме `occurred_at` события — ISO 8601).
- Статусы передаются строками snake_case; отсутствующий статус — `"null"`.

---

## Маршруты (сводная таблица)

| Метод | URL | Контроллер::action | Аутентификация | Scope |
|-------|-----|--------------------|----------------|-------|
| POST | `/orders` | `order/order-intake/create` | HMAC (публичный) | — |
| GET | `/orders` | `order/order/index` | Bearer/Cookie | `orders:read` |
| GET | `/orders/{id}` | `order/order/view` | Bearer/Cookie | `orders:read` |
| GET | `/orders/{id}/events` | `order/order/events` | Bearer/Cookie | `orders:read` |
| GET | `/orders/{id}/deliveries` | `order/order/deliveries` | Bearer/Cookie | `orders:read` |
| POST | `/orders/{id}/status` | `order/order-status/change` | Bearer (M2M/internal) | `orders:report` |
| POST | `/orders/{id}/ack` | `order/order-ack/ack` | Bearer (M2M) | `orders:ack` |
| POST | `/order-inbox` | `order/order-webhook/receive` | HMAC (публичный) | — |
| POST | `/orders/{id}/replay` | `order/order-admin/replay` | Bearer | `orders:admin` |
| POST | `/orders/{id}/archive` | `order/order-admin/archive` | Bearer | `orders:admin` |

---

## POST /orders — создание заказа (intake)

Публичный эндпоинт, защищён HMAC-подписью (см. [integration.md](integration.md)).

### Заголовки

| Заголовок | Описание |
|-----------|----------|
| `Content-Type` | `application/json` |
| `X-Passport-Source` | `website` / `telegram` / `api` / `manual` |
| `X-Passport-Timestamp` | Unix-время |
| `X-Passport-Signature` | `sha256=hash_hmac('sha256', timestamp.body, ORDER_INTAKE_HMAC_SECRET)` |

### Тело

```json
{
  "source": "website",
  "idempotency_key": "site-order-4821",
  "external_ref": "CRM-2026-001",
  "company_id": 5,
  "customer_id": 77,
  "manager_user_id": 12,
  "initial_request": "Нужен лендинг с CRM-формой",
  "currency": "RUB",
  "sla_deadline_at": "2026-11-01 18:00:00"
}
```

Только `source` и `idempotency_key` обязательны.

### Ответы

| Код | Ситуация |
|-----|----------|
| **201** | Запрос обработан; в теле `status=created` или `status=already_exists` |
| **400** | Ошибка валидации |
| **401** | Невалидная HMAC-подпись |

Тело: `{"status":"created","order": <OrderDto>}` или `{"status":"already_exists","order": <OrderDto>}`. HTTP-код в обоих случаях — `201`.

---

## GET /orders — список заказов

Авторизация: Bearer/Cookie, scope `orders:read`, RBAC `orders.list`.

### Query-параметры

| Параметр | Тип | Описание |
|----------|-----|----------|
| `global_status` | string | Фильтр по производному статусу |
| `source` | string | Фильтр по источнику (website/telegram/api/manual) |
| `customer_id` | int | Фильтр по заказчику |
| `manager_user_id` | int | Фильтр по менеджеру |
| `created_from` / `created_to` | datetime | Диапазон создания |
| `limit` | int (1..1000, по умолч. 20) | Размер страницы |
| `page` | int (по умолч. 1) | Номер страницы |

Ответ: `CollectionDto` из `OrderDto` + `_meta` (total/page/limit/pages).

---

## GET /orders/{id} — заказ

Авторизация: scope `orders:read`, RBAC `orders.read`.

- **200** — `{"item": <OrderDto>}`
- **404** — заказ не найден

---

## GET /orders/{id}/events — события

Авторизация: scope `orders:read`, RBAC `orders.read`.

Query-параметр `from_seq` (по умолчанию 0) — вернуть события с порядковым номером ≥ N.

Ответ: `CollectionDto` из `OrderEventDto`.

---

## GET /orders/{id}/deliveries — доставки

Авторизация: scope `orders:read`, RBAC `orders.read`.

Ответ: `CollectionDto` из `OrderDeliveryDto`.

---

## POST /orders/{id}/status — смена статуса

Авторизация: Bearer (M2M-токен или пользователь), scope `orders:report`, RBAC `orders.report`.

### Тело

```json
{
  "field": "production_status",
  "from": "integration",
  "to": "completed",
  "reason": "Заказчик принял работу",
  "payload": { "is_post_release": false }
}
```

| Поле | Обязательно | Описание |
|------|-------------|----------|
| `field` | да | `commercial_status` / `production_status` / `legal_status` / `payment_status` |
| `from` | да | Ожидаемый текущий статус (CAS); для отсутствующего — `"null"` |
| `to` | да | Новый статус |
| `reason` | нет | Причина перехода (попадёт в event log) |
| `payload` | нет | Доп. данные; для `completed → revisions` обязателен `is_post_release: true` |

### Ответы

| Код | Ситуация |
|-----|----------|
| **200** | Статус изменён; тело `{"item": <OrderDto>}` |
| **400** | Недопустимый переход / нарушен precondition |
| **403** | Поле не принадлежит источнику (ownership) |
| **404** | Заказ не найден |
| **409** | CAS-конфликт: фактический статус ≠ `from` |

> Владелец поля определяется автоматически: для M2M — `client_id` из токена, для пользователя — `passport`. Матрица: [architecture.md](architecture.md#матрица-владения-полями-statusownershipregistry).

---

## POST /orders/{id}/ack — подтверждение доставки

Авторизация: Bearer (M2M), scope `orders:ack`, RBAC `orders.ack`.

### Тело

```json
{
  "subscriber_code": "crm",
  "event_id": 42
}
```

- **200** — `{"message": "Delivery acked"}`; доставка → `acked`
- **400** — доставка не найдена или `event_id` не совпадает
- **404** — заказ не найден

---

## POST /order-inbox — входящий вебхук

Публичный эндпоинт, защищён HMAC (секрет подписчика из env). Используется для обратного потока (CRM → Passport).

### Заголовки

| Заголовок | Описание |
|-----------|----------|
| `X-Passport-Source` | `crm` / `legal` / `resume` |
| `X-Passport-Timestamp` | Unix-время |
| `X-Passport-Signature` | HMAC от (timestamp.body) секретом подписчика |

### Тело

```json
{
  "order_id": 123,
  "message_type": "status_report",
  "payload": {
    "field": "production_status",
    "from": "integration",
    "to": "completed",
    "reason": "Акцептовано в CRM"
  },
  "idempotency_key": "crm-status-123-998"
}
```

`message_type`: `status_report` | `comment` | `attachment` | `cost_update`.

- Только `status_report` с полями `field/from/to` в `payload` приводит к смене статуса (через `ChangeStatusHandler`, sourceSystem=crm).
- Жизненный цикл входящего сообщения хранится в `order_inbox.status`: `pending`, `processed`, `failed`. Повтор успешно обработанного ключа возвращает `{"status":"already_exists"}`. Если предыдущая попытка завершилась ошибкой, повторный запрос повторно обрабатывает сохранённые payload и idempotency key; после ошибки сохраняется `failed`.

### Ответы

- **200** — обработан (`status=processed`), повтор неудачной попытки (`status=retried`) или найден уже обработанный ключ (`status=already_exists`)
- **400** — ошибка валидации / заказ не найден
- **401** — невалидная HMAC

---

## POST /orders/{id}/replay — пересоздание доставок

Авторизация: Bearer, scope `orders:admin`, RBAC `orders.admin`.

Без тела. Для всех активных подписчиков, слушающих тип последнего события заказа, пересоздаёт pending-доставки.

Ответ: `CollectionDto` из `OrderDeliveryDto` (актуальные доставки заказа).

---

## POST /orders/{id}/archive — архивация

Авторизация: Bearer, scope `orders:admin`, RBAC `orders.admin`.

### Тело (опционально)

```json
{ "reason": "Закрыт по соглашению сторон" }
```

Действие: `archived_at = now`, событие `OrderArchived` в event log, fan-out подписчикам.

Ответ: `{"item": <OrderDto>}`.

---

## Коды ответов и ошибки

| Код | Значение | Примеры |
|-----|----------|---------|
| 200 | OK | повторный intake, успешные операции |
| 201 | Created | создание заказа |
| 400 | Bad Request | невалидный переход, ошибка валидации |
| 401 | Unauthorized | невалидный токен / HMAC |
| 403 | Forbidden | чужое поле статуса, недостаточный permission/scope |
| 404 | Not Found | заказ не найден |
| 409 | Conflict | CAS-конфликт статуса, конфликт идемпотентности |
| 422 | Unprocessable | недопустимый переход (алиас 400) |
| 500 | Internal | ошибка сервера |

---

## Права доступа (OAuth scopes + RBAC permissions)

### OAuth scopes (добавлены в `modules/oauth/config/params.php`)

| Scope | Назначение |
|-------|------------|
| `orders:read` | Чтение заказов и их событий/доставок |
| `orders:write` | (зарезервирован) создание/изменение заказов |
| `orders:report` | Смена sub-status |
| `orders:ack` | Подтверждение доставки |
| `orders:admin` | replay/archive |

### RBAC permissions (миграция `m261009_000002_add_order_permissions`)

| Permission | Назначение | Роли |
|------------|------------|------|
| `orders.list` | Список заказов | manager, admin, owner |
| `orders.read` | Чтение заказа | manager, admin, owner |
| `orders.report` | Смена статуса | manager, admin, owner |
| `orders.ack` | Подтверждение доставки | manager, admin, owner |
| `orders.admin` | Replay/Archive | admin, owner |

M2M-клиенты (client_credentials) получают доступ через scope'ы; RBAC для них резолвится в `.any`-вариант permission (см. `AuthorizationService::canForIdentity`).

---

## Внутренние команды и запросы (CQRS)

### Команды (`modules/order/application/command/`)

| Команда | Поля | Хендлер |
|---------|------|---------|
| `CreateOrderCommand` | source, idempotencyKey, externalRef?, companyId?, customerId?, managerUserId?, initialRequest?, currency='RUB', slaDeadlineAt?, schemaVersion=1, actorType, actorId?, sourceSystem | `CreateOrderHandler` |
| `ChangeStatusCommand` | orderId, field, from, to, reason?, payload?, actorType, actorId?, sourceSystem, idempotencyKey? | `ChangeStatusHandler` |
| `AckDeliveryCommand` | orderId, subscriberCode, eventId | `AckDeliveryHandler` |
| `ReplayOrderCommand` | orderId | `ReplayOrderHandler` |
| `ArchiveOrderCommand` | orderId, actorType, actorId?, sourceSystem, reason? | `ArchiveOrderHandler` |

### Запросы (`modules/order/application/query/`)

| Запрос | Поля | Хендлер |
|--------|------|---------|
| `GetOrderQuery` | orderId | `GetOrderHandler` |
| `ListOrdersQuery` | filters (globalStatus/source/customerId/managerUserId/createdFrom/createdTo), limit, offset | `ListOrdersHandler` |
| `GetOrderEventsQuery` | orderId, fromSeq | `GetOrderEventsHandler` |
| `GetOrderDeliveriesQuery` | orderId | `GetOrderDeliveriesHandler` |

---

> Дальше: [Подключение систем](integration.md) · [DTO и события](dto-and-events.md)
