# Value Objects ядра

> Документ описывает value objects в `core/domain/valueObject/`. Value objects — неизменяемые объекты, описывающие значения с инвариантами.

## 1. Каталог

| Класс | Файл | Описание |
|---|---|---|
| `AbstractIntId` | valueObject/AbstractIntId.php | База для ID-объектов |
| `RoleId` | valueObject/RoleId.php | ID роли |
| `UserId` | valueObject/UserId.php | ID пользователя |
| `UserRoleId` | valueObject/UserRoleId.php | ID назначения роли |
| `Email` | valueObject/Email.php | Валидный email |
| `RoleName` | valueObject/RoleName.php | Имя роли (правила) |
| `Date` | valueObject/Date.php | Дата (Y-m-d, время обнулено) |
| `IdRange` | valueObject/IdRange.php | Диапазон/набор ID |

## 2. `AbstractIntId`

Базовый класс для идентификаторов.

```php
abstract class AbstractIntId
{
    public function __construct(int $value);  // value < 0 → ValidationException
    public function value(): int;
    public function equals(self $other): bool;   // сравнение по классу и значению
    public function isNew(): bool;               // value === 0
}
```

Производные: `RoleId`, `UserId`, `UserRoleId` — просто наследуют.

## 3. `Email`

```php
final class Email
{
    public function __construct(string $email); // невалидный → ValidationException
    public function value(): string;            // trim'нутый email
}
```

Регулярное выражение валидации:
```regexp
/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
```

## 4. `RoleName`

```php
final class RoleName
{
    public function __construct(string $name); // → ValidationException
    public function value(): string;
}
```

Правила:
- не пустой;
- ≤ 50 символов;
- 3–32 символа из: буквы EN/RU, пробел, дефис, точка, запятая, слэш, амперсанд, плюс.

## 5. `Date`

```php
final class Date
{
    public function __construct(DateTimeImmutable $value); // время обнуляется (00:00:00)
    public static function fromString(string $date): self;  // "Y-m-d"
    public function value(): DateTimeImmutable;
    public function toString(): string;                     // "Y-m-d"
    public function equals(self $other): bool;
}
```

## 6. `IdRange`

Парсит строку с ID/диапазонами в отсортированный уникальный массив int.

### Принимаемые форматы строк:

| Формат | Пример | Результат |
|---|---|---|
| Один ID | `1` | `[1]` |
| Несколько | `1,2,3,4` | `[1,2,3,4]` |
| Диапазон | `2:20` | `[2..20]` |
| Смешанный | `1,3:20` | `[1,3..20]` |
| Смешанный | `1,3:20,27` | `[1,3..20,27]` |

### Методы

```php
public function __construct(string $input);
public static function fromString(?string $input): self;
public static function fromArray(array $ids): self;
public static function empty(): self;
public function toArray(): array;
public function contains(int $id): bool;
public function isEmpty(): bool;
public function count(): int;
public function getFirst(): ?int;
public function getLast(): ?int;
```

### Инварианты / исключения
- `start > end` в диапазоне → `InvalidArgumentException("Invalid range ...")`;
- ID ≤ 0 → `InvalidArgumentException` (положительные только);
- дубликаты сортируются и убираются.

### Использование
- `BaseController::parseIdRangeFromPath(?string $param)` — парсинг `?ids=1,2,3` из query/пути.
- Фильтры DTO (`RoleFiltersDto`, `UserFiltersDto`, `UserRoleFiltersDto`) используют `ids` как строку `IdRange`.