<?php

namespace core\domain\valueObject;

/**
 * Input: string
 * Пример принимаемых для парсинга форматов строк:
 * - 1
 * - 1,2,3,4
 * - 2:20
 * - 1,3:20
 * - 1,3:20,27
 */
final class IdRange
{
    private array $ids;

    public function __construct(string $input)
    {
        $this->ids = $this->parse($input);
    }

    private function parse(string $input): array
    {
        if (trim($input) === '') {
            return [];
        }

        $result = [];
        $parts = explode(',', $input);

        foreach ($parts as $part) {
            $part = trim($part);

            if (str_contains($part, ':')) {
                [$start, $end] = explode(':', $part);

                $start = (int)$start;
                $end = (int)$end;

                if ($start > $end) {
                    throw new \InvalidArgumentException("Invalid range {$part}");
                }

                $result = array_merge($result, range($start, $end));
            } else {
                $value = (int)$part;

                if ($value <= 0) {
                    throw new \InvalidArgumentException("Invalid id {$part}");
                }

                $result[] = $value;
            }
        }

        return array_values(array_unique($result));
    }

    public function toArray(): array
    {
        return $this->ids;
    }

    public function contains(int $id): bool
    {
        return in_array($id, $this->ids, true);
    }

    public function isEmpty(): bool
    {
        return empty($this->ids);
    }
}
