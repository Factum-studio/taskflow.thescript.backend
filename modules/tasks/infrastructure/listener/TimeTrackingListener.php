<?php

namespace modules\tasks\infrastructure\listener;

use modules\tasks\domain\event\TimerStoppedEvent;
use modules\tasks\domain\repository\IDailySummaryRepository;
use modules\tasks\domain\valueObject\Date;
use modules\tasks\domain\valueObject\Duration;
use modules\tasks\domain\valueObject\TaskId;
use modules\tasks\domain\valueObject\UserId;
use Yii;

class TimeTrackingListener
{
    private IDailySummaryRepository $dailySummaryRepository;

    public function __construct(IDailySummaryRepository $dailySummaryRepository)
    {
        $this->dailySummaryRepository = $dailySummaryRepository;
    }

    public function handleTimerStopped(TimerStoppedEvent $event): void
    {
        $taskId = new TaskId($event->getTaskId());
        $userId = new UserId($event->getUserId());
        $date = Date::fromString($event->getStoppedAt()->format('Y-m-d'));
        $duration = new Duration($event->getDuration());

        $summary = $this->dailySummaryRepository->findOrCreate($taskId, $userId, $date);
        $summary->addTime($duration);
        $this->dailySummaryRepository->save($summary);

        Yii::info("Daily summary updated for task {$event->getTaskId()} user {$event->getUserId()} date {$date->toString()}: +{$event->getDuration()}s", 'tasks');
    }
}