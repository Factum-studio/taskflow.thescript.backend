<?php

namespace core\domain\entity;

use core\domain\ValueObject\Identify;
use core\domain\ValueObject\Post;
use core\domain\ValueObject\Contact;

class User
{
    public function __construct(
        private readonly Identify $id,
        private readonly string $surname,
        private readonly string $name,
        private readonly ?string $patronymic,
        private readonly int $dob,
        private readonly array $contacts,
        private readonly ?Post $post = null
    ) {}

    public function getId(): Identify
    {
        return $this->id;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    public function getFullName(): string
    {
        return trim($this->surname . ' ' . $this->name . ' ' . $this->patronymic);
    }

    public function getUiName(): string
    {
        return isset($this->patronymic) ?
            trim($this->name . ' ' . $this->patronymic) :
            trim($this->surname . ' ' . $this->name);
    }

    public function getDob(): int
    {
        return $this->dob;
    }

    /**
     * @return Contact[]
     */
    public function getContacts(): array
    {
        return $this->contacts;
    }

    /**
     * Получить контакт по типу (tg, phone, email и т.д.)
     */
    public function getContactByType(string $type): ?Contact
    {
        foreach ($this->contacts as $contact) {
            if ($contact->getType() === $type) {
                return $contact;
            }
        }
        return null;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }
}