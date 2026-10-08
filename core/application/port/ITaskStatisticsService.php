<?php
namespace core\application\port;

// TODO: relocate to anal module
interface ITaskStatisticsService
{
    public function getTotalTasks(): int;
}