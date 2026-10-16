# Локальная копия "Доставка событий и ретраи"

> Как события доходят до внешних систем, что происходит при сбоях, какие ретраи и гарантии.

---

## Содержание

- [Жизненный цикл доставки](#жизненный-цикл-доставки)
- [Формат вебхука](#формат-вебхука)
- [Политика ретраев (DeliveryWorker)](#политика-ретраев-deliveryworker)
- [Классификация ответов подписчика](#классификация-ответов-подписчика)
- [Идемпотентность доставки](#идемпотентность-доставки)
- [Ack и роль подписчика](#ack-и-роль-подписчика)
- [Replay (админ)](#replay-админ)
- [Гарантии и ограничения](#гарантии-и-ограничения)
- [Мониторинг](#мониторинг)

---

## Жизненный цикл доставки

```
событие в order_events
        │
        ▼
FanOutEventHandler → upsertPending(order, subscriber, event_id)
        │
        ▼
order_deliveries: status=pending, attempts=0
        │
        ▼
DeliveryWorker (cron, каждые ~30 c)
        │  claimPending(): FOR UPDATE SKIP LOCKED
        ▼
HTTP POST webhook_url (HMAC-подпись)
        │
        ▼
    ┌── 2xx / 409 ──────→ status=delivered
    ├── 429 ────────────→ status=failed, next_retry_at=+Retry-After
    ├── 5xx ────────────→ status=failed, next_retry_at=+backoff
    ├── 4xx (кроме 409) → status=dead
    └── network/timeout → status=failed, next_retry_at=+backoff

подписчик шлёт POST /orders/{id}/ack → status=acked
```

---

## Формат вебхука

`DeliveryWorker` отправляет **POST** на `webhook_url` подписчика:

### Заголовки

| Заголовок | Значение |
|-----------|----------|
| `Content-Type` | `application/json` |
| `X-Passport-Signature` | `sha256=hash_hmac('sha256', timestamp . '.' . body, secret)` |
| `X-Passport-Timestamp` | Unix-время (секунды) |
| `X-Passport-Event-Id` | ID события в `order_events` |
| `X-Passport-Event-Type` | Тип события (например `OrderCreated`) |
| `X-Passport-Schema-Version` | Версия контракта payload |
| `Idempotency-Key` | `<order_id>:<event_id>:<subscriber_code>` — подписчик должен использовать его для дедупликации |

### Тело (JSON-конверт, `EnvelopeBuilder`)

```json
{
  "event_id": 42,
  "sequence_number": 7,
  "event_type": "OrderCreated",
  "order_id": 123,
  "occurred_at": "2026-10-08T12:34:56+00:00",
  "schema_version": 1,
  "source_system": "passport",
  "payload": { "...": "..." }
}
```

Как правильно принимать эти вебхуки — см. [webhook-consumer-guide.md](webhook-consumer-guide.md).

---

## Политика ретраев (DeliveryWorker)

Константы (параметры env):

| Параметр | env | По умолчанию |
|----------|-----|--------------|
| `delivery_max_attempts` | `ORDER_DELIVERY_MAX_ATTEMPTS` | 8 |
| `delivery_timeout_seconds` | `ORDER_DELIVERY_TIMEOUT` | 10 |
| `hmac_tolerance_seconds` | `ORDER_HMAC_TOLERANCE` | 300 |
| `post_release_window_days` | `ORDER_POST_RELEASE_WINDOW_DAYS` | 30 |

### Экспоненциальный backoff

При каждой retry-попытке задержка до следующей попытки:

```
delay = min(2 ^ attempts, 3600) + random(0, 30) секунд
```

То есть: 1-я ретрая ≈ 2с, 2-я ≈ 4с, 3-я ≈ 8с, ..., потолок 1 час + джиттер до 30с.

### Переходы attempts → статус

| attempts | Статус | Действие |
|----------|--------|----------|
| 0→... | `failed` | next_retry_at = now + backoff |
| = MAX_ATTEMPTS (8) | `dead` | Пометка dead + error в лог; **дальше ретраев нет** |

---

## Классификация ответов подписчика

| Ответ | Статус доставки | Комментарий |
|-------|-----------------|-------------|
| **2xx** | `delivered` | Успех |
| **409 Conflict** | `delivered` | Подписчик уже обработал событие (идемпотентность) — считаем доставленным |
| **429 Too Many Requests** | `failed` | Ретрай через `Retry-After` (или 60с по умолчанию) |
| **5xx** | `failed` | Ретрай с экспоненциальным backoff |
| **4xx (кроме 409)** | `dead` | Ошибка клиента (неверный URL/формат) — ретраить бессмысленно |
| **network / timeout** | `failed` | Ретрай с backoff |
| **неизвестный/3xx** | `failed` | Ретрай с backoff |

---

## Идемпотентность доставки

- **Заголовок `Idempotency-Key`** уникален для каждой пары (событие, подписчик): `order_id:event_id:subscriber_code`.
- Подписчик **обязан** использовать его: если событие уже обработано, можно вернуть `409` — Passport посчитает доставку успешной.
- Повторная доставка одного события возможна (например после сбоя сети), поэтому обработка на стороне подписчика должна быть идемпотентной.

---

## Ack и роль подписчика

После успешной обработки подписчик может подтвердить получение:

```
POST /orders/{id}/ack
Authorization: Bearer <M2M token>
{
  "subscriber_code": "crm",
  "event_id": 42
}
```

- Ответ `200` — доставка переведена в `acked`.
- `event_id` должен совпадать с последним доставленным событием для этого подписчика.
- **Ack не обязателен** для семантики доставки, но полезен для аудита и диагностики.

---

## Replay (админ)

Если подписчик потерял события (например пересоздали окружение):

```
POST /orders/{id}/replay
Authorization: Bearer <admin token>
```

Действие: для всех **активных** подписчиков, слушающих тип последнего события заказа, пересоздаются pending-доставки (last_event_id = последнее событие). Воркер доставит их заново.

---

## Гарантии и ограничения

| Свойство | Значение |
|----------|----------|
| **At-least-once** | ✅ Да. Событие доставляется минимум один раз (возможны дубли — защита идемпотентностью) |
| **Exactly-once** | ❌ Нет (как и у большинства event bus) — подписчик должен дедуплицировать по `Idempotency-Key` |
| **Порядок событий** | ✅ Сохраняется: `sequence_number` + один DeliveryWorker на доставку (SKIP LOCKED) |
| **Multi-worker** | ✅ `FOR UPDATE SKIP LOCKED` гарантирует, что одну доставку возьмёт только один воркер |
| **Потеря событий** | ❌ Невозможна при штатной работе: событие пишется в `order_events` в той же транзакции, что и изменение заказа (после коммита доставка ставится в очередь) |
| **Терминальный dead** | Требует ручного вмешательства админа (replay или исправление конфигурации подписчика) |

---

## Мониторинг

- `order_deliveries.status` — мгновенная картина: сколько `pending/processing/failed/delivered/acked/dead`. Состояние `processing` означает, что запись захвачена worker-процессом; застрявшие записи автоматически доступны для повторного захвата после истечения lease.
- Доставки `dead` — признаки ошибок на стороне подписчика (URL, формат) или неверных секретов → проверить конфигурацию подписчика и логи.
- `DeliveryWorker` пишет в лог: `warning` при ретрае, `error` при dead.
- `findStale()` — поиск «зависших» pending (старше N минут) для диагностики.

---

> Дальше: [Администрирование](administration.md) · [Миграции и откаты](migrations.md)
