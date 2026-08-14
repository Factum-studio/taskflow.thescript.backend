<?php

namespace core\domain\valueObject;

final class Contact
{
    private string $type; // tg, phone, email, vk, discord
    private string $value;
    private bool $confirmed;

    /**
     * @param string $type
     * @param string $value
     * @param bool $confirmed
     */
    public function __construct(
        string $type,
        string $value,
        bool $confirmed
    ) {
        $this->type = $type;
        $this->value = $value;
        $this->confirmed = $confirmed;
    }


    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }


}