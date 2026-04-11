<?php

namespace app\commands;

use core\application\notification\Notification;
use core\application\notification\NotificationHub;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class EmailSendCommand extends Controller
{
    private NotificationHub $notificationHub;

    public function __construct($id, $module, NotificationHub $notificationHub, $config = [])
    {
        $this->notificationHub = $notificationHub;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(string $email, string $subject, string $body): int
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->stderr("Некорректный email: {$email}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $notification = new Notification('email', $email, $subject, $body);

        try {
            $this->notificationHub->send($notification);
            $this->stdout("✓ Уведомление отправлено на {$email}\n", Console::FG_GREEN);
            return ExitCode::OK;
        } catch (\Throwable $e) {
            $this->stderr("Ошибка при отправке\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}