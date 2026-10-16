# Локальная копия "Гайд для внешних систем: приём вебхуков и ответные части"

> Этот документ предназначен для команд, которые интегрируют **внешнюю систему** (CRM, legal, resume или другую) с модулем orders в Passport. Здесь — как принимать исходящие вебхуки от Passport, как проверять подпись, как отвечать, как отправлять статусы обратно, как подтверждать получение, а также требования надёжности.

---

## Содержание

- [Общая картина](#общая-картина)
- [Часть A. Приём вебхуков от Passport](#часть-a-приём-вебхуков-от-passport)
  - [A1. URL эндпоинта](#a1-url-эндпоинта)
  - [A2. Формат запроса](#a2-формат-запроса)
  - [A3. Проверка подписи (обязательно)](#a3-проверка-подписи-обязательно)
  - [A4. Идемпотентность](#a4-идемпотентность)
  - [A5. Ответы Passport](#a5-ответы-passport)
- [Часть B. Отправка статусов обратно (ответные части)](#часть-b-отправка-статусов-обратно-ответные-части)
  - [B1. Через M2M API](#b1-через-m2m-api)
  - [B2. Через входящий вебхук /order-inbox](#b2-через-входящий-вебхук-order-inbox)
  - [B3. Какие статусы ваша система может писать](#b3-какие-статусы-ваша-система-может-писать)
- [Часть C. Подтверждение доставки (ack)](#часть-c-подтверждение-доставки-ack)
- [Часть D. Чтение данных](#часть-d-чтение-данных)
- [Часть E. Требования надёжности](#часть-e-требования-надёжности)
- [Часть F. Обработка ошибок и логирование](#часть-f-обработка-ошибок-и-логирование)
- [Часть G. Пример реализации (псевдокод)](#часть-g-пример-реализации-псевдокод)
- [Чек-лист для разработчика внешней системы](#чек-лист-для-разработчика-внешней-системы)

---

## Общая картина

```
Passport (источник событий)                Ваша система (подписчик)
─────────────────────────────              ────────────────────────
order_events ──► deliveries ──►            POST /webhook/passport  (Часть A)
                                   ◄────   обработка события
                                   ────►   POST /orders/{id}/status (Часть B)
                                   ────►   POST /orders/{id}/ack    (Часть C)
```

Вы получаете события, обрабатываете их, сообщаете о своих действиях (в рамках вашей зоны владения) и подтверждаете получение.

---

## Часть A. Приём вебхуков от Passport

### A1. URL эндпоинта

Вы указываете `webhook_url` при регистрации подписчика (см. [integration.md](integration.md)). Passport шлёт **POST** на этот URL. Эндпоинт должен:

- принимать `POST` с `Content-Type: application/json`;
- быть доступен из сети Passport (не за firewall без необходимости);
- отвечать быстро (Passport ждёт до 10 секунд — `delivery_timeout_seconds`).

### A2. Формат запроса

**Заголовки:**

| Заголовок | Пример | Обязателен для проверки |
|-----------|--------|--------------------------|
| `Content-Type` | `application/json` | — |
| `X-Passport-Signature` | `sha256=5f9c...` | ✅ да |
| `X-Passport-Timestamp` | `1791739753` | ✅ да |
| `X-Passport-Event-Id` | `42` | для логов/дедупликации |
| `X-Passport-Event-Type` | `OrderCreated` | для логов/маршрутизации |
| `X-Passport-Schema-Version` | `1` | для совместимости |
| `Idempotency-Key` | `123:42:crm` | ✅ для дедупликации |

**Тело (JSON-конверт):**

```json
{
  "event_id": 42,
  "sequence_number": 7,
  "event_type": "OrderCreated",
  "order_id": 123,
  "occurred_at": "2026-10-08T12:34:56+00:00",
  "schema_version": 1,
  "source_system": "passport",
  "payload": {
    "order_id": 123,
    "source": "website",
    "external_ref": null,
    "company_id": 5,
    "customer_id": 77,
    "manager_user_id": 12,
    "initial_request": "Нужен лендинг",
    "currency": "RUB"
  }
}
```

Полный список событий и их payload — в [dto-and-events.md](dto-and-events.md).

### A3. Проверка подписи (обязательно)

1. Прочитайте `X-Passport-Timestamp` (Unix-секунды). Если `|now - timestamp| > 300` секунд — **отклонить** (защита от replay).
2. Прочитайте `X-Passport-Signature`: формат `sha256=<hex>`.
3. Вычислите ожидаемую подпись:

```
expected = "sha256=" + hex( HMAC_SHA256( secret, timestamp + "." + raw_body ) )
```

4. Сравните через константно-временную функцию (`hash_equals` в PHP, `crypto.timingSafeEqual` в Node, `hmac.compare_digest` в Python).

```python
# Python (пример)
import hmac, hashlib, time

secret = b"ВАШ_СЕКРЕТ_ИЗ_ENV"          # LEGAL_HMAC_SECRET / CRM_HMAC_SECRET
ts     = request.headers["X-Passport-Timestamp"]
sig    = request.headers["X-Passport-Signature"]
body   = request.get_data()            # СЫРОЕ тело байт-в-байт!

expected = "sha256=" + hmac.new(secret, f"{ts}.".encode() + body, hashlib.sha256).hexdigest()
if abs(time.time() - int(ts)) > 300:   reject(401)
if not hmac.compare_digest(expected, sig): reject(401)
```

> ⚠️ **Критично:** подпись считается от **сырого тела** (raw body), до любой нормализации/перекодирования JSON. Если ваш фреймворк сам парсит JSON — берите тело до парсинга.

### A4. Идемпотентность

- Passport использует доставку **at-least-once**: одно и то же событие может прийти **несколько раз** (сеть, ретраи).
- Заголовок `Idempotency-Key` имеет вид `<order_id>:<event_id>:<subscriber_code>` и уникален для пары (событие, подписчик).
- **Вы обязаны** хранить обработанные ключи (БД unique-колонка или key-value) и при повторном получении **не обрабатывать повторно**.

### A5. Ответы Passport

| Ваш ответ | Что сделает Passport |
|-----------|----------------------|
| `2xx` (200/201/202/204) | Доставка `delivered` — событие считается доставленным |
| `409 Conflict` | Доставка `delivered` (Passport считает, что вы уже обработали событие по Idempotency-Key) |
| `429 Too Many Requests` (+ `Retry-After`) | Ретрай через `Retry-After` (или 60с) |
| `5xx` | Ретрай с экспоненциальным backoff (2, 4, 8, ... до 1ч, максимум 8 попыток) |
| `4xx` (кроме 409) | Доставка `dead` — **ретраев не будет** (ошибка неисправима со стороны Passport) |

**Правило:** если вы можете обработать событие — верните `2xx`. Если уже обработали — верните `409`. Никогда не возвращайте `4xx` для временных проблем (лучше `5xx` или `429`).

**Важно про retry:** ретраи **не** означают, что вы можете отвечать медленно. Passport ждёт ответ до 10 секунд, затем считает network-timeout и ретраит.

---

## Часть B. Отправка статусов обратно (ответные части)

У вашей системы есть **зона владения** — определённые поля статусов, которые только вы можете писать. Матрица:

| Поле | Кто пишет |
|------|-----------|
| `production_status` | **CRM** |
| `legal_status` | **Legal** (временно также внутренние пользователи Passport) |
| `commercial_status`, `payment_status` | Внутренние пользователи Passport (не подписчики!) |

Если вы попытаетесь писать чужое поле — получите `403 StatusOwnershipException`.

### B1. Через M2M API

Самый прямой способ. Получите M2M-токен:

```bash
curl -X POST https://passport.example.com/token \
  -d "grant_type=client_credentials" \
  -d "client_id=<ваш_m2m_client_id>" \
  -d "client_secret=<ваш_client_secret>" \
  -d "scope=orders:report"
```

Затем меняйте статус:

```bash
curl -X POST https://passport.example.com/orders/123/status \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
        "field": "production_status",
        "from": "integration",
        "to": "completed",
        "reason": "Акцептовано в CRM"
      }'
```

**Обязательные правила:**

- `from` — **текущий статус заказа** (CAS). Если не знаете — сначала `GET /orders/{id}`. При `409` — перечитать и повторить с актуальным `from`.
- Для ещё не начатых полей (production/legal/payment) `from` = **строка `"null"`**.
- Для rollback `completed → revisions`: `payload: {"is_post_release": true}` (только в окне 30 дней).

| Ответ | Значение |
|-------|----------|
| `200` | Статус изменён |
| `400` | Недопустимый переход / нарушен precondition (например `null→in_progress` без commercial=agreed) |
| `403` | Поле не ваше (ownership) |
| `404` | Заказ не найден |
| `409` | CAS-конфликт (from устарел) |

### B2. Через входящий вебхук /order-inbox

Альтернатива для систем, которые не хотят работать с OAuth-токенами, но имеют HMAC-секрет:

```bash
curl -X POST https://passport.example.com/order-inbox \
  -H "Content-Type: application/json" \
  -H "X-Passport-Source: crm" \
  -H "X-Passport-Timestamp: <unix>" \
  -H "X-Passport-Signature: sha256=<...>" \
  -d '{
        "order_id": 123,
        "message_type": "status_report",
        "payload": {
          "field": "production_status",
          "from": "integration",
          "to": "completed"
        },
        "idempotency_key": "crm-st-123-998"
      }'
```

Подпись: `sha256=hex(hmac_sha256(secret, "<timestamp>.<raw_body>"))` где secret — ваш HMAC-секрет (тот же, что в env Passport).

`message_type` может быть `status_report` | `comment` | `attachment` | `cost_update`. Только `status_report` с `field/from/to` в payload меняет статус (в зоне вашего владения).

> Рекомендуется **M2M API** (B1) для систем, где уже есть OAuth-интеграция; **/order-inbox** — для простых систем (боты, скрипты) без токенов.

### B3. Какие статусы ваша система может писать

| Система | Может писать | Требования/Preconditions |
|---------|--------------|--------------------------|
| CRM | `production_status` | `null→in_progress` только если `commercial_status=agreed`; `completed→revisions` только в окне 30 дней + `is_post_release=true` |
| Legal | `legal_status` | `signed→archived` только если `global_status ∈ {completed, refunded}` |
| Resume | — | Только чтение |

---

## Часть C. Подтверждение доставки (ack)

Рекомендуется (не обязательно): после успешной обработки события подтвердите получение.

```bash
curl -X POST https://passport.example.com/orders/123/ack \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
        "subscriber_code": "crm",
        "event_id": 42
      }'
```

- `subscriber_code` — ваш system_code (`crm`/`legal`/`resume`).
- `event_id` — из `X-Passport-Event-Id` (или `payload.event_id`).
- Ответ `200` → доставка `acked`; `400` — если event_id не совпадает.

---

## Часть D. Чтение данных

| Endpoint | Назначение | Scope |
|----------|------------|-------|
| `GET /orders?global_status=...&source=...` | Список заказов с фильтрами | `orders:read` |
| `GET /orders/{id}` | Заказ | `orders:read` |
| `GET /orders/{id}/events?from_seq=N` | Event log с порядкового номера | `orders:read` |
| `GET /orders/{id}/deliveries` | Матрица доставок | `orders:read` |

Используйте `GET /orders/{id}/events` для «догона» пропущенных событий (например после простоя или потери вебхуков).

---

## Часть E. Требования надёжности

| Требование | Почему |
|------------|--------|
| **Идемпотентность обработки** | Passport доставляет at-least-once, дубли возможны |
| **Дедупликация по `Idempotency-Key`** | Единственный надёжный механизм против дублей |
| **Ответ 2xx/409 как можно быстрее** | Таймаут Passport 10 сек; медленный ответ = ретрай |
| **Не возвращать 4xx на временные сбои** | 4xx (кроме 409) = dead, ретраев больше не будет |
| **Проверять подпись всегда** | Иначе ваш эндпоинт открыт для подделки событий |
| **Проверять свежесть timestamp** | Защита от replay-атак |
| **Обрабатывать события в порядке sequence_number?** | Желательно: `sequence_number` в конверте; порядок гарантируется в рамках заказа |
| **Готовность к ретраям** | Событие может прийти повторно через секунды или часы (backoff до 1ч) |

---

## Часть F. Обработка ошибок и логирование

Логируйте на своей стороне:

- `X-Passport-Event-Id`, `X-Passport-Event-Type`, `order_id`, `Idempotency-Key`;
- результат проверки подписи (ok / reject + причина);
- результат обработки (ok / duplicate / error).

При ошибках:

- **Несоответствие подписи** → ответ `401` (Passport пометит доставку `dead`, вы увидите в `last_error`).
- **Временная ошибка обработки** (БД лежит, внешний сервис недоступен) → ответ `5xx` (Passport ретраит).
- **Перегрузка** → `429` + `Retry-After`.
- **Невалидный payload (схема не совпала)** → `4xx` (Passport пометит dead — это значит, что конфигурация/версия контракта требует внимания).

---

## Часть G. Пример реализации (псевдокод)

```php
// PHP (Yii2/любой фреймворк) — эндпоинт приёма вебхуков
public function actionWebhook(): Response
{
    $rawBody = Yii::$app->request->getRawBody(); // сырое тело!
    $headers = Yii::$app->request->getHeaders();

    $timestamp = (string)$headers->get('X-Passport-Timestamp');
    $signature = (string)$headers->get('X-Passport-Signature');

    // 1. Свежесть
    if (abs(time() - (int)$timestamp) > 300) {
        return $this->json(['error' => 'stale timestamp'], 401);
    }

    // 2. Подпись
    $expected = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $rawBody, getenv('PASSPORT_HMAC_SECRET'));
    if (!hash_equals($expected, $signature)) {
        return $this->json(['error' => 'bad signature'], 401);
    }

    // 3. Идемпотентность
    $idemKey = (string)$headers->get('Idempotency-Key');
    if (ProcessedEvent::find()->where(['idem_key' => $idemKey])->exists()) {
        return $this->json(['status' => 'duplicate'], 409); // уже обработано
    }

    // 4. Обработка (в транзакции: пометить ключ + применить событие)
    try {
        $event = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        $this->applyEvent($event);            // ваша бизнес-логика
        ProcessedEvent::create(['idem_key' => $idemKey, 'event_id' => $event['event_id']]);
        return $this->json(['status' => 'ok'], 200);
    } catch (Throwable $e) {
        Yii::error(['webhook' => $e->getMessage(), 'idem' => $idemKey], 'passport-webhook');
        return $this->json(['error' => 'temporary'], 503); // ретрай
    }
}
```

> Обратите внимание: пометка `Idempotency-Key` и применение события — **в одной транзакции**, иначе между ними может вклиниться повторная доставка.

---

## Чек-лист для разработчика внешней системы

- [ ] Эндпоинт `POST` на `webhook_url` реализован и доступен из сети Passport
- [ ] Подпись проверяется по сырому телу (raw body) + свежесть timestamp
- [ ] Идемпотентность по `Idempotency-Key` с транзакционной пометкой
- [ ] Ответ 2xx/409 при успехе/дубле; 429/5xx при временных проблемах; 4xx только на неисправимое
- [ ] M2M-клиент создан, scopes `orders:read,orders:report,orders:ack` выданы
- [ ] Секрет в env совпадает с хешем в `order_subscribers`
- [ ] Реализована отправка статусов через `POST /orders/{id}/status` (CAS: `from` всегда актуальный)
- [ ] Реализован `POST /orders/{id}/ack` (рекомендуется)
- [ ] Настроено логирование event_id/order_id/idempotency_key
- [ ] Прогон сквозного сценария: создание заказа → получение вебхука → смена статуса → ack

---

> См. также: [integration.md](integration.md) (подключение со стороны Passport) · [dto-and-events.md](dto-and-events.md) (форматы) · [delivery-and-retries.md](delivery-and-retries.md) (гарантии доставки).
