<?php
namespace core\application\dto;

class CompanyUserDto implements \JsonSerializable
{
    public function __construct(
        public int $userId,
        public string $surname,
        public string $name,
        public ?string $patronymic,
        public ?string $post,
        public ?string $email,
        public ?string $avatar
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'userId' => $this->userId,
            'surname' => $this->surname,
            'name' => $this->name,
            'patronymic' => $this->patronymic,
            'post' => $this->post,
            'email' => $this->email,
            'avatar' => $this->avatar,
        ];
    }
}