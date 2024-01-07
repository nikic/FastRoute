<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher\Result;

use ArrayAccess;
use FastRoute\Dispatcher;
use OutOfBoundsException;
use RuntimeException;

/** @implements ArrayAccess<int, Dispatcher::METHOD_NOT_ALLOWED|non-empty-list<string>> */
final class MethodNotAllowed implements ArrayAccess
{
    /**
     * @readonly
     * @var non-empty-list<string> $allowedMethods
     */
    public array $allowedMethods;

    public function offsetExists(mixed $offset): bool
    {
        return $offset === 0 || $offset === 1;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return match ($offset) {
            0 => Dispatcher::METHOD_NOT_ALLOWED,
            1 => $this->allowedMethods,
            default => throw new OutOfBoundsException(),
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
