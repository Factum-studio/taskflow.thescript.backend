# Конфигурация модуля feedback

> Документ описывает DI-регистрацию, маршруты и подписки модуля feedback.

## 1. `config/di.php`

```php
$container = Yii::$container;

$container->setSingleton(IFeedbackRatingRepository::class, function () {
    return new DbFeedbackRatingRepository();
});

$container->setSingleton(IFeedbackIdeaRepository::class, function () {
    return new DbFeedbackIdeaRepository();
});

// Глобальная подписка на событие ядра/модуля
$globalDispatcher = Yii::$container->get(GlobalEventDispatcher::class);
$globalDispatcher->addListener(RatingSubmittedEvent::class, SendNonMaxRatingEmailListener::class);
```

Ключевое: модуль **не имеет собственного** `ModuleEventDispatcher` — использует глобальную шину ядра напрямую.

## 2. `config/routing.php`

```php
return [
    'POST feedback/rating' => 'feedback/feedback/submit-rating',
    'POST feedback/idea'   => 'feedback/feedback/submit-idea',
    'GET  feedback/idea'   => 'feedback/feedback/ideas',
    'GET  feedback/stats'  => 'feedback/feedback/stats',
];
```

## 3. Контроллер

`FeedbackController` (единственный) — действия:
- `submit-rating` (POST /feedback/rating)
- `submit-idea` (POST /feedback/idea)
- `ideas` (GET /feedback/idea)
- `stats` (GET /feedback/stats)

Наследует `BaseController` (единые обёртки ответов: `item`, `collection`, `error`).

## 4. Порядок подключения

- `modules/feedback/config/di.php` — в `$diConfigs` (config/web.php).
- `modules/feedback/config/routing.php` — в `urlManager.rules` (config/web.php).
- Модуль регистрируется в `config/modules.php` (`'feedback' => [...]`).
- Миграции — в `config/migration_namespaces.php`.

## 5. Что задаётся в коде (не конфиг)

| Что | Где |
|---|---|
| Тема и текст письма «Помогите нам стать лучше» | SendNonMaxRatingEmailListener |
| «Максимальность» оценки: `value >= 4` | VO `Rating::isMax()` |
| Разрешённые типы идей | VO `IdeaType` |
| Каналы по умолчанию `['email','push']` | BaseNotificationListener::getChannels() |
| Диапазон оценки 0–5 | VO `Rating` (+CHECK-constraints в миграции) |