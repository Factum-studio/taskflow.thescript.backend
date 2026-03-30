<?php

namespace core\application\notification\channel;

use core\application\notification\INotification;
use Yii;
use Redis;

class PushChannel
{
    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function send(INotification $notification): bool
    {
        if ($notification->getType() !== 'push') {
            return false;
        }

        $userId = $notification->getRecipient();
        $data = json_encode([
            'event' => 'notification',
            'data' => [
                'subject' => $notification->getSubject(),
                'body' => $notification->getBody(),
                'context' => $notification->getContext(),
            ],
        ]);

        $channel = "user:{$userId}";
        $this->redis->publish($channel, $data);

        return true;
    }
}