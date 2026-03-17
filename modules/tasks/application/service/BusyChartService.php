<?php

namespace modules\tasks\application\service;

use DateTimeImmutable;
use InvalidArgumentException;
use modules\tasks\application\dto\BusySegmentDto;
use modules\tasks\domain\entity\TimeInterval;

class BusyChartService
{
    /**
     * Построить диаграмму занятости из интервалов
     * @param TimeInterval[] $intervals
     * @param string $granularity 'minute', 'ten_minutes', 'hour'
     * @param string $mode 'merged', 'separate', 'overlap'
     * @return BusySegmentDto[]
     */
    public function build(array $intervals, string $granularity, string $mode): array
    {
        $rounded = $this->roundIntervals($intervals, $granularity);

        return match ($mode) {
            'merged' => $this->mergeIntervals($rounded),
            'separate' => $this->separateIntervals($rounded),
            'overlap' => $this->findOverlaps($rounded),
            default => throw new InvalidArgumentException("Unknown mode: $mode"),
        };
    }

    /**
     * Округление start_time и end_time до указанной точности
     * Например, для ten_minutes: округлить вниз/вверх до ближайших 10 минут
     * Возвращает массив объектов с start, end, taskId
     */
    private function roundIntervals(array $intervals, string $granularity): array
    {
        $result = [];
        foreach ($intervals as $interval) {
            $start = $this->roundTime($interval->getStartTime(), $granularity, 'floor');
            $end = $interval->getEndTime()
                ? $this->roundTime($interval->getEndTime(), $granularity, 'ceil')
                : null;
            if ($end && $start < $end) {
                $result[] = (object)[
                    'start' => $start,
                    'end' => $end,
                    'taskId' => $interval->getTaskId()->getValue(),
                ];
            }
        }
        return $result;
    }

    private function roundTime(DateTimeImmutable $time, string $granularity, string $mode): DateTimeImmutable
    {
        $minutes = (int)$time->format('i');
        $seconds = (int)$time->format('s');
        $totalMinutes = $minutes + $seconds / 60;

        switch ($granularity) {
            case 'minute':
                return $time->setTime((int)$time->format('H'), $minutes);
            case 'ten_minutes':
                $rounded = floor($totalMinutes / 10) * 10;
                if ($mode === 'ceil') {
                    $rounded = ceil($totalMinutes / 10) * 10;
                }
                return $time->setTime((int)$time->format('H'), (int)$rounded);
            case 'hour':
                if ($mode === 'floor') {
                    return $time->setTime((int)$time->format('H'), 0);
                } else {
                    return $time->setTime((int)$time->format('H') + 1, 0);
                }
            default:
                return $time;
        }
    }

    private function mergeIntervals(array $rounded): array
    {
        usort($rounded, fn($a, $b) => $a->start <=> $b->start);
        $merged = [];
        foreach ($rounded as $item) {
            if (empty($merged)) {
                $merged[] = clone $item;
                continue;
            }
            $last = $merged[count($merged)-1];
            if ($item->start <= $last->end) {
                // пересекаются или соприкасаются
                $last->end = max($last->end, $item->end);
                $last->taskId = array_unique(array_merge((array)$last->taskId, [$item->taskId]));
            } else {
                $merged[] = clone $item;
            }
        }
        return array_map(fn($seg) => new BusySegmentDto(
            $seg->start->format('Y-m-d H:i:s'),
            $seg->end->format('Y-m-d H:i:s'),
            is_array($seg->taskId) ? $seg->taskId : [$seg->taskId]
        ), $merged);
    }

    private function separateIntervals(array $rounded): array
    {
        return array_map(fn($item) => new BusySegmentDto(
            $item->start->format('Y-m-d H:i:s'),
            $item->end->format('Y-m-d H:i:s'),
            [$item->taskId]
        ), $rounded);
    }

    private function findOverlaps(array $rounded): array
    {
        $events = [];
        foreach ($rounded as $item) {
            $events[] = ['time' => $item->start, 'type' => 'start', 'taskId' => $item->taskId];
            $events[] = ['time' => $item->end, 'type' => 'end', 'taskId' => $item->taskId];
        }
        usort($events, fn($a, $b) => $a['time'] <=> $b['time'] ?: ($a['type'] === 'start' ? -1 : 1));

        $active = [];
        $result = [];
        $lastTime = null;
        foreach ($events as $event) {
            $currentTime = $event['time'];
            if ($lastTime !== null && $currentTime > $lastTime && !empty($active)) {
                $result[] = new BusySegmentDto(
                    $lastTime->format('Y-m-d H:i:s'),
                    $currentTime->format('Y-m-d H:i:s'),
                    array_values($active)
                );
            }
            if ($event['type'] === 'start') {
                $active[$event['taskId']] = $event['taskId'];
            } else {
                unset($active[$event['taskId']]);
            }
            $lastTime = $currentTime;
        }
        return $result;
    }
}