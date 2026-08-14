<?php

namespace core\application\dto;

use core\domain\entity\User;
use core\domain\valueObject\Contact;

class UserDto implements \JsonSerializable
{
    private function __construct(
        private readonly array $data
    ) {}

    public static function fromEntity(User $user): self
    {
        $contacts = array_map(
            fn(Contact $contact) => [
                'type' => $contact->getType(),
                'value' => $contact->getValue(),
                'confirmed' => $contact->isConfirmed(),
            ],
            $user->getContacts()
        );

        $post = $user->getPost() ? [
            'id' => $user->getPost()->getId(),
            'name' => $user->getPost()->getName(),
        ] : null;

        return new self([
            'id' => $user->getId()->value(),
            'surname' => $user->getSurname(),
            'name' => $user->getName(),
            'patronymic' => $user->getPatronymic(),
            'ui_name' => $user->getUiName(),
            'full_name' => $user->getFullName(),
            'dob' => $user->getDob(),
            'contacts' => $contacts,
            'post' => $post,
        ]);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}