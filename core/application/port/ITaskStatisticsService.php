<?php
namespace core\application\port;

interface ITaskStatisticsService
{
    public function getTotalTasks(): int;
}