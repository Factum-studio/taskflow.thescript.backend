# Архитектура проекта TaskFlow CRM API

## Обзор архитектуры

Проект построен на основе **модульной монолитной архитектуры** с использованием паттернов 
**Domain-Driven Design (DDD)** и **Clean Architecture**. 
Код организован по принципу разделения ответственности с явным выделением предметных областей.

```
api/
├ core/       # Ядро системы (общая инфраструктура и абстракции)
├ modules/    # Бизнес-модули (предметные области / bounded contexts)
└ config/     # Конфигурация приложения
```
---

## Структура core (Ядро системы)

Ядро содержит общую инфраструктуру и абстракции, используемые всеми модулями. Реализует сквозную функциональность (cross-cutting concerns).

```
core/
├ application/      # Слой приложения
│ ├ dto/             # Объекты передачи данных (DTO)
│ ├ port/            # Интерфейсы портов (входные/выходные)
│ ├ service/         # Сервисы приложения
│ └ useCase/         # Бизнес-сценарии (Use Cases)
├ domain/           # Слой предметной области ядра
│ ├ exception/       # Исключения домена
│ └ valueObject/     # Value Objects (Identity, JwtToken и др.)
├ infrastructure/   # Слой инфраструктурной реализации
│ ├ handler/         # Обработчики
│ ├ http/            # HTTP клиенты и конфигурация
│ ├ jwt/             # JWT аутентификация
│ └ passport/        # Интеграция с Passport
├ presentation/     # Слой представления
│ ├ controller/      # Базовые контроллеры
│ ├ swagger/         # Общая Swagger документация
│ └ view/            # View компоненты
├ security/         # Безопасность
└ event/            # События ядра
```
---

### Ключевые компоненты core


| Компонент                    | Назначение                           |
|------------------------------|--------------------------------------|
| `AuthenticateByJwtUseCase`   | Базовый сценарий JWT аутентификации  |
| `JwtMiddleware`              | Middleware для проверки JWT токенов  |
| `BaseController`             | Базовый контроллер с общей логикой   |
| `JsonErrorHandler`           | Централизованная обработка ошибок    |
| `Identity`, `JwtToken`       | Value Objects для идентификации      |

---

## Структура modules (Бизнес-модули)

Каждый модуль представляет отдельную предметную область и следует единой внутренней структуре.

### Доступные модули

```
modules/
├ analytics/    # Аналитика и отчёты
├ clients/      # Управление клиентами
├ files/        # Файловое хранилище
├ kanban/       # Канбан-доски
├ projects/     # Управление проектами
└ tasks/        # Задачи и таски
```

### Стандартная структура модуля

```
[module]/
├ application/      # Слой приложения
│ ├ command/         # Команды (CQRS)
│ ├ dto/             # DTO модуля
│ ├ handler/         # Обработчики команд/запросов
│ └ query/           # Запросы (CQRS)
├ domain/           # Слой предметной области
│ ├ entity/          # Сущности
│ ├ event/           # Доменные события
│ ├ repository/      # Интерфейсы репозиториев
│ ├ service/         # Доменные сервисы
│ └ valueObject/     # Value Objects
├ infrastructure/   # Слой инфраструктуры
│ ├ listener/        # Слушатели событий
│ ├ migrations/      # Миграции БД
│ ├ persistence/     # Persistence модели
│ └ repository/      # Реализации репозиториев
├ presentation/     # Слой представления
│ ├ controller/      # REST контроллеры
│ └ request/         # Request объекты
└ config/           # Конфигурация модуля
```
---

## Событийная шина (Event Bus)

В проекте используется два уровня событий:

1. **Локальный диспетчер** (`ModuleEventDispatcher`) – обрабатывает события внутри модуля.
2. **Глобальный диспетчер** (`GlobalEventDispatcher`) – позволяет подписываться на события из любого модуля.

Модули могут форвардить свои доменные события в глобальную шину через `DispatchingEventDecorator`.
Это позволяет реализовать кросс-модульные сценарии (например, аналитика, уведомления, аудит) без прямых зависимостей.

### Форвардимые события

#### Модуль Projects
- `ProjectCreatedEvent`
- `ProjectMemberAddedEvent`
- `ProjectMemberRemovedEvent`
- `BoardCreatedEvent`
- `InvitationCreatedEvent`
- `InvitationAcceptedEvent`
- `InvitationCancelledEvent`

#### Модуль Tasks
- `TaskCreatedEvent`
- `TaskAssignedEvent`
- `TaskMovedToColumnEvent`
- `TaskUpdatedEvent`
- `TaskSoftDeletedEvent`
- `TaskRestoredEvent`
- `CommentAddedEvent`
- `CommentUpdatedEvent`
- `StickerAttachedToTaskEvent`
- `TimerStartedEvent`
- `TimerStoppedEvent`
- `IntervalLoggedEvent`

Все эти события доступны для подписки из других модулей через `GlobalEventDispatcher`.

---

## Структура config (Конфигурация)

Централизованная конфигурация приложения Yii2.

| Файл                       | Назначение                            |
|----------------------------|---------------------------------------|
| `web.php`                  | Конфигурация веб-приложения           |
| `console.php`              | Конфигурация консольных команд        |
| `db.php`                   | Настройки подключения к БД            |
| `modules.php`              | Регистрация модулей                   |
| `container.php`            | DI контейнер (внедрение зависимостей) |
| `params.php`               | Параметры приложения                  |
| `passport_http_client.php` | Настройки HTTP клиента для Passport   |
| `migration_namespaces.php` | Пространства имён миграций            |
| `test.php`, `test_db.php`  | Конфигурация для тестирования         |

---

## Архитектурные диаграммы

### Взаимодействие компонентов

![Компонентная диаграмма](https://www.plantuml.com/plantuml/png/RS_1JiCm30RWUvx2x3vntm5j6XLCcc3hoWEuhGUHqgJO3X9lpxBqa5RSeZzV_jXRKMIDWpCFFLsj9wYhVRDxUpbMq3aKCOQfK_IVZc3rsZGdgEaxakWAK83pkJGd9Y6lg3PuEE8lyLsEHLpotJWwsoqrvdI9dyE0jdHz2cRauA-CeXmxZKm5jRCBqwDYYv9AfrCkR8sPbeBr-casDcoWhYuDx4u7CyiJd2QUt3vuvU0SCTpkFeJsA6GAazxawHD_v-wBH1cLeF9grP8izM81sAHQg6aCOZLmUZ4N-Ols1Y_du-CvqjtaF_ylh7x8gnYp9T_7mVq1)

### Схема чистой архитектуры модуля

![Clean Architecture модуля](https://www.plantuml.com/plantuml/png/TP5HImCn48JVxrU4lhU_GAHY2nOiLH7VrzpsY2QRkDa8HVlVlVO8nIHz2zyiCyna6GLOahDT0Fq90whPrlV9uapzT4enQW4Qx8YV62EIW1XFawYKzYvu6gcwzoJihKKkX1VyIXXbfCVkl-5T2DRegtxE0VK5VKx8Xu8zZ6TrxEfrruXSUGUcaROcCL8Qlu5Dk7l_G2rLf-2Z4S-73Gdo7ZGM5rFG-XkfrNn3UuOedBGadfQ-vk-2BSH74mMfAhmr0v-tp7cN7r7p-MqUj1BofldWn2wRvjUPJYiLiBr5WwgRtyMYhZQPdgoAqNBFIZfsIwG-EVi7)

### Поток данных (CQRS)

![CQRS Flow](https://www.plantuml.com/plantuml/png/TOzFImCn4CNl-HH3JteelNkGuX_qfkqgdiVieonkacKoAHRnkvjqkvN82ibbte_tuwsZicXolM6hGUWrGemPs7xm3hIutMoR1NqPelm4Ljdl-d8Fph5lgUbQZ3cH3Mu87bxfbCvV7_Fl8SUmPULtZg0twHHtD_duRMhJ9a4E_tYEsTnjrh45joT5bfIXj5nUqpJbYfwVcXTQyT2j134aiJXJOsrc7kx_8cOgA5YzCVE318iOBovD7HLwMfsno7janeoQi_2t1v-mIGjJ1OKikyOIzB1QQgRYEZvuky8UNgDPmxV9zJy0)

---

## Архитектурные принципы

### 1. Чистая архитектура
- **Domain** - ядро бизнес-логики (не зависит от внешних слоёв)
- **Application** - сценарии использования (оркестрирует домен)
- **Infrastructure** - внешние сервисы (БД, API, файлы)
- **Presentation** - интерфейсы (REST API)

### 2. CQRS
- Разделение команд (изменение состояния) и запросов (чтение)
- Команды в `application/command/`
- Запросы в `application/query/`

### 3. DDD
- Чёткие границы контекстов (каждый модуль)
- Богатая доменная модель с сущностями и value objects
- Репозитории для работы с агрегатами

### 4. Порты и адаптеры
- Порты (интерфейсы) в `application/port/`
- Адаптеры (реализации) в `infrastructure/`
---

## Правила зависимостей

- Модули **не должны** зависеть друг от друга напрямую
- Взаимодействие модулей только через **Core** или события
- Core **не должен** зависеть от модулей
- Инфраструктура зависит от интерфейсов (портов), а не наоборот
---

## Создание нового модуля

1. Скопируйте установленную структуру модуля (можно готового например, `tasks`)
2. Зарегистрируйте модуль в `config/modules.php`
3. Определите маршруты в конфигурации модуля (файл `config/routing.php` в самом модуле)
4. Реализуйте доменную модель
5. Добавьте миграции при необходимости
---

## Тестирование

- **Unit тесты** - для Domain и Application слоёв
- **Integration тесты** - для Infrastructure
- **API тесты** - для Presentation
---

## Заключение об использовании

Данная архитектура обеспечивает:
- **Масштабируемость** - лёгкое добавление новых модулей
- **Тестируемость** - чёткие границы и инверсия зависимостей
- **Поддерживаемость** - разделение ответственности
- **Гибкость** - возможность заменить инфраструктурные реализации
---
