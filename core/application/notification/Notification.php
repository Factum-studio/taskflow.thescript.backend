<?php

namespace core\application\notification;

class Notification implements INotification
{
    private string $type;
    private string $recipient;
    private string $subject;
    private string $body;
    private array $context;

    public function __construct(string $type, string $recipient, string $subject, string $body, array $context = [])
    {
        $this->type         = $type;
        $this->recipient    = $recipient;
        $this->subject      = $subject;
        $this->body         = $body;
        $this->context      = $context;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}