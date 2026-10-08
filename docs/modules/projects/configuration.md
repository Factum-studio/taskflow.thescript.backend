# Конфигурация модуля projects

> Документ описывает DI-регистрацию, маршруты и подписки модуля projects.

## 1. `config/di.php`

Регистрирует:

**Репозитории**

| Порт | Реализация | Зависимость |
|---|---|---|
| `IProjectRepository` | `DbProjectRepository` | `Yii::$app->db` |
| `IBoardRepository` | `DbBoardRepository` | `Yii::$app->db` |
| `IProjectUserRepository` | `DbProjectUserRepository` | `Yii::$app->db` |
| `IInvitationRepository` | `DbInvitationRepository` | `Yii::$app->db` |

**Ассемблеры**: `ProjectDtoAssembler`, `BoardDtoAssembler`, `ProjectUserDtoAssembler`.

**Доступ**: `IProjectAccess` → `ProjectAccess`.

**Слушатели**: `AddOwnerAsMemberListener`, `SendInvitationEmailListener` (+ регистрируются как классы).

**Шина событий модуля** (`ModuleEventDispatcher`):

```php
$listeners = [
    ProjectCreatedEvent::class          => [[$ownerListener, 'handleProjectCreated'], [$logger, 'handleProjectCreated']],
    ProjectMemberAddedEvent::class      => [[$logger, 'handleProjectMemberAdded']],
    ProjectMemberRemovedEvent::class    => [[$logger, 'handleProjectMemberRemoved']],
    BoardCreatedEvent::class            => [[$logger, 'handleBoardCreated']],
    InvitationCreatedEvent::class       => [[$logger, 'handleInvitationCreated'], [$invitationListener, 'handle']],
    InvitationAcceptedEvent::class      => [[$logger, 'handleInvitationAccepted']],
    InvitationCancelledEvent::class     => [[$logger, 'handleInvitationCancelled']],
];
```

**Forward-события** (пробрасываются в глобальную шину через `DispatchingEventDecorator`):

```php
$forwardEvents = [
    ProjectCreatedEvent::class,
    ProjectMemberAddedEvent::class,
    ProjectMemberRemovedEvent::class,
    BoardCreatedEvent::class,
    InvitationCreatedEvent::class,
    InvitationAcceptedEvent::class,
    InvitationCancelledEvent::class,
];
```

**Глобальная подписка** (в конце файла):

```php
$globalDispatcher = Yii::$container->get(GlobalEventDispatcher::class);
$globalDispatcher->addListener(UserFirstLoginEvent::class, CreatePersonalProjectOnUserFirstLogin::class);
```

## 2. `config/routing.php`

```php
return [
    // Проекты
    'GET project'                    => 'projects/project/index',
    'GET project/<id:\d+>'           => 'projects/project/view',
    'POST project'                   => 'projects/project/create',
    'PUT project/<id:\d+>'           => 'projects/project/update',
    'DELETE project/<id:\d+>'        => 'projects/project/delete',

    // Доски
    'GET project/<projectId:\d+>/board'  => 'projects/board/index',
    'GET board/<id:\d+>'                 => 'projects/board/view',
    'POST project/<projectId:\d+>/board' => 'projects/board/create',
    'PUT board/<id:\d+>'                 => 'projects/board/update',
    'DELETE board/<id:\d+>'              => 'projects/board/delete',

    // Участники
    'GET project/<projectId:\d+>/member'   => 'projects/project-member/index',
    'POST project/<projectId:\d+>/member'  => 'projects/project-member/create',
    'PUT project/<projectId:\d+>/member/<userId:\d+>/role' => 'projects/project-member/update-role',
    'DELETE project/<projectId:\d+>/member/<userId:\d+>'   => 'projects/project-member/delete',

    // Приглашения
    'POST project/<id:\d+>/invite' => 'projects/invitation/invite',
    'POST invitation/accept'       => 'projects/invitation/accept',
    'DELETE project/<id:\d+>/invitation' => 'projects/invitation/cancel',
];
```

Подключается в `config/web.php` (array_merge в urlManager.rules), DI — в `$diConfigs`.

## 3. Что задаётся в коде (не конфиг)

| Что | Где |
|---|---|
| `MAX_USER_PROJECTS = 10` | ProjectAccess |
| `MAX_PROJECT_BOARDS = 4` | ProjectAccess |
| Правила по типам проектов (personal/collaborative/corporate) | ProjectAccess |
| Имя личного проекта `«Личный проект пользователя #<id>»`, тип `personal` | CreatePersonalProjectOnUserFirstLogin |
| Текст письма приглашения | SendInvitationEmailListener |
| Лимиты/правила валидации имён (≤255) | сущность Project |

## 4. Контроллеры

| Контроллер | Маршруты (префикс) |
|---|---|
| `ProjectController` | `/project*` |
| `BoardController` | `/project/<projectId>/board*`, `/board*` |
| `ProjectMemberController` | `/project/<projectId>/member*` |
| `InvitationController` | `/project/<id>/invite`, `/invitation/accept`, `/project/<id>/invitation` |

Все контроллеры наследуют `BaseController` (единые обёртки ответов).