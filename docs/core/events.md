# События ядра (core)

Глобальный диспетчер событий (`GlobalEventDispatcher`) позволяет подписываться на события из любого модуля.

## Список событий

### `UserFirstLoginEvent`
**Когда возникает**: при первом входе пользователя (создаётся локальная запись в таблице `user`).

**Поля**:
- `userId` (int) – ID пользователя из Passport
- `occurredAt` (DateTimeImmutable)

**Обработчики**:
- `CreatePersonalProjectOnUserFirstLogin` (модуль Projects) – создаёт личный проект.

## Как подписаться на событие

В конфигурации DI (например, `config/container.php`):

$globalDispatcher = Yii::$container->get(GlobalEventDispatcher::class);
$globalDispatcher->addListener(UserFirstLoginEvent::class, YourListener::class);

Где `YourListener` должен иметь метод `handle($event)`.

## Планируемые события
- `FileUploadedEvent`
- `AnalyticsEvent`