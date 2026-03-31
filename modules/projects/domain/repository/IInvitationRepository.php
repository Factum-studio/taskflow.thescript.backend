<?php

namespace modules\projects\domain\repository;

use modules\projects\domain\entity\Invitation;
use modules\projects\domain\valueObject\ProjectId;

interface IInvitationRepository
{
    public function save(Invitation $invitation): Invitation;
    public function findById(int $id): ?Invitation;
    public function findByToken(string $token): ?Invitation;
    public function findPendingByProjectAndEmail(ProjectId $projectId, string $email): ?Invitation;
    public function deleteExpired(): int;
    public function remove(Invitation $invitation): void;
}