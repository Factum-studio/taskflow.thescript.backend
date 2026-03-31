# Инструкция для фронтендеров: подключение к API

## Базовый URL
- Локальная разработка: `http://localhost:8080`
- Тестовый сервер: уточните у администратора
- Продакшн: `https://api.taskflow.thescript.agency`

## Аутентификация

### Получение JWT токена
`POST /user/login`

**Тело запроса** (JSON):
```
{
   "login": "user@example.com",
   "password": "password123"
}
```

**Успешный ответ (200)**:
```
{
   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
   "expires_in": 3600
}
```

**Ошибка (401)**:
```
{
   "error": {
      "code": 401,
      "message": "Invalid credentials"
   }
}
```

### Использование токена
Во все защищённые запросы добавляйте заголовок:
`Authorization: Bearer <your-jwt-token>`

## Real-time уведомления (SSE)

1. Получите одноразовый токен для SSE:
   `GET /events/token` (требует обычного JWT)

   Ответ: `{"token": "sse-token"}`

2. Подключитесь к потоку событий:
   `GET /events?token=<sse-token>`
   Content-Type: `text/event-stream`
   Connection: keep-alive

3. События приходят в формате:
   event: message
   data: {"event":"notification","data":{"subject":"...","body":"...","context":{}}}

   Событие `error` при проблемах.

## Основные эндпоинты (примеры)

| Метод  | URL                           | Описание                      |
|--------|-------------------------------|-------------------------------|
| GET    | `/project`                    | Список проектов пользователя  |
| POST   | `/project`                    | Создать проект                |
| GET    | `/project/{id}/board`         | Доски проекта                 |
| POST   | `/project/{id}/board`         | Создать доску                 |
| GET    | `/board/{id}`                 | Получить доску                |
| PUT    | `/board/{id}`                 | Обновить доску                |
| DELETE | `/board/{id}`                 | Удалить доску                 |
| GET    | `/board/{boardId}/column`     | Колонки доски                 |
| POST   | `/board/{boardId}/column`     | Создать колонку               |
| GET    | `/task`                       | Список задач (с фильтрацией)  |
| POST   | `/task`                       | Создать задачу                |
| GET    | `/task/{id}`                  | Получить задачу               |
| PUT    | `/task/{id}`                  | Обновить задачу               |
| DELETE | `/task/{id}`                  | Мягкое удаление               |
| POST   | `/task/{id}/move-to-column`   | Переместить задачу            |
| POST   | `/task/{id}/assign`           | Назначить исполнителя         |
| POST   | `/task/{id}/restore`          | Восстановить задачу           |
| DELETE | `/task/{id}/hard`             | Жёсткое удаление              |
| GET    | `/task-priority`              | Справочник приоритетов        |
| GET    | `/sticker`                    | Список стикеров               |
| POST   | `/sticker`                    | Создать стикер                |
| GET    | `/time-interval`              | Список интервалов времени     |
| POST   | `/time-interval/start`        | Запустить таймер              |
| POST   | `/time-interval/stop`         | Остановить таймер             |
| POST   | `/time-interval`              | Ручной ввод интервала         |
| GET    | `/task/{taskId}/time-summary` | Диаграмма занятости по задаче |

Полная документация доступна в Swagger: `/docs/swagger`

## Обработка ошибок

Все ошибки возвращаются в формате JSON:

```
{
   "error": {
      "code": 422,
      "message": "Validation failed",
      "details": { "title": ["Title is required"] }
   }
}
```

Коды HTTP:
- 200 – успех
- 201 – создано (не используется, всегда 200)
- 400 – ошибка запроса
- 401 – неавторизован (токен отсутствует или недействителен)
- 403 – доступ запрещён (роль не позволяет)
- 404 – ресурс не найден
- 422 – ошибка валидации (details содержит поля)
- 500 – внутренняя ошибка сервера

## Версионирование

Текущая версия API: `v1` (неявно, через префикс в маршрутах отсутствует). В будущем планируется `/api/v1/...`

## Кэширование через Redis

CRM может использовать Redis для кэширования:
- Данных пользователя из Passport (TTL 1 час)
- (В перспективе) кэша запросов, сессий, очередей

Для включения Redis-кэша замените в `config/web.php` и `config/console.php` компонент `cache` на:

```
'cache' => [
   'class' => 'yii\redis\Cache',
   'redis' => [
      'hostname' => $_ENV['REDIS_HOST'],
      'port' => $_ENV['REDIS_PORT'],
      'password' => $_ENV['REDIS_PASSWORD'],
      'database' => 0,
      'usePredis' => false, // или true, если нет ext-redis
   ],
],
```

После этого не забудьте установить расширение `yiisoft/yii2-redis` и настроить окружение.