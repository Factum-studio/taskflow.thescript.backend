<?php

declare(strict_types=1);

namespace modules\company\domain\entity;

use core\domain\valueObject\UserId;
use DateTimeImmutable;
use modules\company\domain\valueObject\CompanyId;
use modules\company\domain\valueObject\CompanyInvitationStatus;
use InvalidArgumentException;

class CompanyInvitation
{
    private int $id;
    private CompanyId $companyId;
    private string $email;
    private UserId $invitedBy;
    private string $token;
    private CompanyInvitationStatus $status;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $expiresAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        int $id,
        CompanyId $companyId,
        string $email,
        UserId $invitedBy,
        string $token,
        CompanyInvitationStatus $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $expiresAt,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->id = $id;
        $this->companyId = $companyId;
        $this->setEmail($email);
        $this->invitedBy = $invitedBy;
        $this->token = $token;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    // -------- Getters --------

    public function getId(): int
    {
        return $this->id;
    }
    public function getCompanyId(): CompanyId
    {
        return $this->companyId;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getInvitedBy(): UserId
    {
        return $this->invitedBy;
    }
    public function getToken(): string
    {
        return $this->token;
    }
    public function getStatus(): CompanyInvitationStatus
    {
        return $this->status;
    }
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // -------- Mutators --------

    public function accept(): void
    {
        if (!$this->status->isPending()) {
            throw new InvalidArgumentException('Only pending invitations can be accepted');
        }
        if ($this->isExpired()) {
            throw new InvalidArgumentException('Invitation has expired');
        }
        $this->status = new CompanyInvitationStatus(CompanyInvitationStatus::ACCEPTED);
        $this->touch();
    }

    public function cancel(): void
    {
        if (!$this->status->isPending()) {
            throw new InvalidArgumentException('Only pending invitations can be cancelled');
        }
        $this->status = new CompanyInvitationStatus(CompanyInvitationStatus::CANCELLED);
        $this->touch();
    }

    public function isExpired(): bool
    {
        return new DateTimeImmutable() > $this->expiresAt;
    }

    public function isPending(): bool
    {
        return $this->status->isPending() && !$this->isExpired();
    }

    /**
     * @internal For repository use only
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    private function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }
        $this->email = $email;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
