<?php

namespace modules\projects\domain\entity;

use DateTimeImmutable;
use modules\projects\domain\valueObject\InvitationStatus;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use InvalidArgumentException;

class Invitation
{
    private int $id;
    private ProjectId $projectId;
    private string $email;
    private UserId $invitedBy;
    private string $token;
    private InvitationStatus $status;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $expiresAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        int $id,
        ProjectId $projectId,
        string $email,
        UserId $invitedBy,
        string $token,
        InvitationStatus $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $expiresAt,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id           = $id;
        $this->projectId    = $projectId;
        $this->setEmail($email);
        $this->invitedBy    = $invitedBy;
        $this->token        = $token;
        $this->status       = $status;
        $this->createdAt    = $createdAt;
        $this->expiresAt    = $expiresAt;
        $this->updatedAt    = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getProjectId(): ProjectId { return $this->projectId; }
    public function getEmail(): string { return $this->email; }
    public function getInvitedBy(): UserId { return $this->invitedBy; }
    public function getToken(): string { return $this->token; }
    public function getStatus(): InvitationStatus { return $this->status; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getExpiresAt(): DateTimeImmutable { return $this->expiresAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function accept(): void
    {
        if (!$this->status->isPending()) {
            throw new InvalidArgumentException('Only pending invitations can be accepted');
        }
        if ($this->isExpired()) {
            throw new InvalidArgumentException('Invitation has expired');
        }
        $this->status = new InvitationStatus(InvitationStatus::ACCEPTED);
        $this->touch();
    }

    public function expire(): void
    {
        if (!$this->status->isPending()) {
            throw new InvalidArgumentException('Only pending invitations can be expired');
        }
        $this->status = new InvitationStatus(InvitationStatus::EXPIRED);
        $this->touch();
    }

    public function cancel(): void
    {
        if (!$this->status->isPending()) {
            throw new InvalidArgumentException('Only pending invitations can be cancelled');
        }
        $this->status = new InvitationStatus(InvitationStatus::CANCELLED);
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

    /**
     * @internal For repository use only
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}