<?php

declare(strict_types=1);

namespace core\domain\valueObject;

use core\domain\exception\ValidationException;

final class RoleName
{
    private string $value;

    /**
     * @throws ValidationException
     */
    public function __construct(string $name)
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            throw new ValidationException('Role name cannot be empty');
        }
        if (strlen($trimmed) > 50) {
            throw new ValidationException('Role name too long');
        }
        if (!preg_match('/^[A-Za-zА-Яа-яЁё\s\-\.\,\/&+]{3,32}$/u', $trimmed)) {
            throw new ValidationException(
                'Role name must be 3-32 characters (letters English/Russian, spaces, hyphens, dots, commas, slashes, ampersands and plus signs).',
            );
        }
        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }
}
