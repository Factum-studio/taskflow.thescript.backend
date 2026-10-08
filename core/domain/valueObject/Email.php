<?php

declare(strict_types=1);

namespace core\domain\valueObject;

use core\domain\exception\ValidationException;

final class Email
{
    private string $value;

    /**
     * @throws ValidationException
     */
    public function __construct(string $email)
    {
        $trimmed = trim($email);
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $trimmed)) {
            throw new ValidationException('Invalid email address');
        }
        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }
}
