<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher\Result;

use ArrayAccess;
use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use OutOfBoundsException;
use RuntimeException;

/**
 * @phpstan-import-type ExtraParameters from DataGenerator
 * @implements ArrayAccess<int, Dispatcher::FOUND|mixed|array<string, string>>
 */
final class Matched implements ArrayAccess
{
    /** @readonly */
    public mixed $handler;

    /**
     * @readonly
     * @var array<string, string> $variables
     */
    public array $variables = [];

    /**
     * @readonly
     * @var ExtraParameters
     */
    public array $extraParameters = [];

    public function offsetExists(mixed $offset): bool
    {
        return $offset >= 0 && $offset <= 2;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return match ($offset) {
            0 => Dispatcher::FOUND,
            1 => $this->handler,
            2 => $this->variables,
            default => throw new OutOfBoundsException()
        };
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Result cannot be changed');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Result cannot be changed');
    }
}
