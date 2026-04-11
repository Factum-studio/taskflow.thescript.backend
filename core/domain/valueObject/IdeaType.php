<?php
namespace core\domain\valueObject;

use InvalidArgumentException;

final class IdeaType
{
    public const IDEA = 'idea';
    public const BUG = 'bug';
    public const FEATURE = 'feature';
    public const IMPROVEMENT = 'improvement';
    public const QUESTION = 'question';

    private const ALLOWED = [
        self::IDEA,
        self::BUG,
        self::FEATURE,
        self::IMPROVEMENT,
        self::QUESTION,
    ];

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::ALLOWED, true)) {
            throw new InvalidArgumentException('Invalid idea type');
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}