# Уведомления (NotificationHub и каналы)

> Документ описывает систему уведомлений: интерфейс, каналы, NotificationHub, регистрацию и использование.

## 1. Интерфейс `INotification`

```php
namespace core\application\notification;

interface INotification
{
    public function getType(): string;      // 'email', 'telegram', 'sms'
    public function getRecipient(): string; // email, telegram_id, phone
    public function getSubject(): string;
    public function getBody(): string;
    public function getContext(): array;    // дополнительные данные
}
```

## 2. Реализация `Notification`

```php
class Notification implements INotification
```

Конструктор: `new Notification(string $type, string $recipient, string $subject, string $body, array $context = [])`.

## 3. `NotificationHub`

```php
class NotificationHub
```

Хранит три канала: `EmailChannel`, `TelegramChannel`, `PushChannel`.

### `send(INotification $notification): void`

1. По `$notification->getType()` выбирает канал:
   - `'email'` → `EmailChannel`
   - `'telegram'` → `TelegramChannel`
   - `'push'` → `PushChannel`
2. Вызывает `$channel->send($notification)`.
3. Если успех → `false` — логирует `warning`.
4. При `\Throwable` — логирует `error`.

### DI-конфигурация (config/container.php)

| Канал | Зависимости |
|---|---|
| `EmailChannel` | `$_ENV['SENDER_EMAIL']`, `$_ENV['SENDER_NAME']` |
| `TelegramChannel` | — (синглтон без зависимостей) |
| `PushChannel` | `Redis` (instance) |

## 4. Каналы

### 4.1. `EmailChannel`

**Файл:** `core/application/notification/channel/EmailChannel.php`

- Использует `Mailer` Yii2 (`yii/symfonymailer/Mailer`).
- Настроен через `config/web.php` и `config/console.php` (транспорт SMTP).

**Параметры из `.env`:**

| Переменная | Назначение |
|---|---|
| `MAILER_SCHEME` | `smtp` |
| `MAILER_HOST` | SMTP-хост |
| `MAILER_PORT` | SMTP-порт (465) |
| `MAILER_USERNAME` | Логин |
| `MAILER_PASSWORD` | Пароль |
| `MAILER_ENCRYPTION` | `ssl`, `tls` |
| `SENDER_EMAIL` | Адрес отправителя |
| `SENDER_NAME` | Имя отправителя |

### 4.2. `TelegramChannel`

**Файл:** `core/application/notification/channel/TelegramChannel.php`

Каркас. В текущей реализации не подключен к реальному API Telegram. В `NotificationHub` объявлен, но не имеет настоящего транспорта. Планируется к доработке.

### 4.3. `PushChannel` (через Redis)

**Файл:** `core/application/notification/channel/PushChannel.php`

- Использует `Redis` для публикации push-сообщений.
- Потребитель push-канала (WebSocket/SSE со стороны клиента) **не реализован**.

### 4.4. `SmsChannel`

**Файл:** `core/application/notification/channel/SmsChannel.php`

Не используется в `NotificationHub` (отсутствует в конструкторе). Файл существует, но не зарегистрирован. Планируется к доработке.

## 5. Кто использует уведомления

| Модуль / Хендлер | Какой канал | Цель |
|---|---|---|
| `projects` → `SendInvitationEmailListener` | email | Отправка приглашения в проект |
| `feedback` → `SendNonMaxRatingEmailListener` | email | Письмо при не максимальной оценке |

Оба используют `NotificationHub` для отправки.

## 6. Как отправить уведомление (пример)

```php
use core\application\notification\NotificationHub;
use core\application\notification\Notification;

$hub = Yii::$container->get(NotificationHub::class);
$hub->send(new Notification(
    'email',                        // тип
    'user@example.com',             // получатель
    'Тема письма',                  // тема
    'Текст письма',                  // тело
    []                              // контекст (опционально)
));
```