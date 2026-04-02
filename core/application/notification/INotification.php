<?php

namespace core\application\notification;

interface INotification
{
    public function getType(): string; // 'email', 'telegram', 'sms'
    public function getRecipient(): string; // email, telegram_id, phone
    public function getSubject(): string;
    public function getBody(): string;
    public function getContext(): array; // дополнительные данные
}