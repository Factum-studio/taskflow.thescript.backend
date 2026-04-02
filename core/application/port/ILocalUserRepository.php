<?php

namespace core\application\port;

interface ILocalUserRepository
{
    public function exists(int $userId): bool;
    public function create(int $userId, string $email): void;
    public function getEmail(int $userId): ?string;
    public function getPost(int $userId): ?string;
    public function getCompanyId(int $userId): ?int;
    public function getCreatedAt(int $userId): ?\DateTimeImmutable;
}