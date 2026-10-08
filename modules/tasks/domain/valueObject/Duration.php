<?php

declare(strict_types=1);

namespace modules\tasks\domain\valueObject;

use InvalidArgumentException;

// Warning: be careful, we find suspicious todo in this file
final class Duration
{
    private int $seconds;

    public function __construct(int $seconds)
    {
        if ($seconds < 0) {
            throw new InvalidArgumentException('Duration cannot be negative');
        }
        $this->seconds = $seconds;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function toHoursMinutes(): string
    {
        $hours = floor($this->seconds / 3600);
        $minutes = floor(($this->seconds % 3600) / 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
