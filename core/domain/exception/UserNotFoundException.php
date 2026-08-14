<?php

namespace core\domain\exception;

final class UserNotFoundException extends \DomainException
{
    public function __construct(string $message = 'User not found')
    {
        parent::__construct($message);
    }
}