<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use FastRoute\RouteInterface;
use RuntimeException;

/**
 * Result Object
 */
class Result implements ResultInterface
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    protected bool $matched = false;

    protected ?RouteInterface $route;

    /** @var mixed[] */
    protected array $result = [];

    protected int $status = self::NOT_FOUND;

    /** @var mixed */
    protected $handler;

    /** @var mixed[] */
    protected array $args = [];

    /** @var string[] */
    protected array $allowedMethods = [];

    /**
     * @param mixed $handler
     */
    public function __construct(
        int $status = self::NOT_FOUND,
        $handler = null,
        ?RouteInterface $route = null
    ) {
        $this->status = $status;
        $this->handler = $handler;
        $this->route = $route;
    }

    public static function createFound(RouteInterface $route): Result
    {
        return new self(
            self::FOUND,
            $route->handler(),
            $route
        );
    }

    public static function createNotFound(): Result
    {
        $self = new self();
        $self->result = [self::NOT_FOUND];
        $self->status = self::NOT_FOUND;

        return $self;
    }

    /**
     * @param string[] $allowedMethods
     */
    public static function createMethodNotAllowed(array $allowedMethods): Result
    {
        $self = new self();
        $self->result = [self::METHOD_NOT_ALLOWED, $allowedMethods];
        $self->status = self::METHOD_NOT_ALLOWED;
        $self->allowedMethods = $allowedMethods;

        return $self;
    }

    /**
     * @param mixed[] $result Result
     */
    public static function fromArray(array $result): Result
    {
        $self = new self();
        $self->result = $result;
        $self->status = $result[0];

        if ($result[0] === self::FOUND) {
            $self->handler = $result[1];
            $self->args = $result[2];
            $self->route = $result[3];
        }

        return $self;
    }

    /**
     * @return mixed
     */
    public function handler()
    {
        return $this->result[1] ?? null;
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function args()
    {
        return $this->result[2] ?? [];
    }

    public function routeMatched(): bool
    {
        return $this->result[0] === self::FOUND;
    }

    public function methodNotAllowed(): bool
    {
        return $this->result[0] === self::METHOD_NOT_ALLOWED;
    }

    public function routeNotFound(): bool
    {
        return $this->result[0] === self::NOT_FOUND;
    }

    /**
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->result[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->result[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException(
            'You can\'t mutate the state of the result'
        );
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException(
            'You can\'t mutate the state of the result'
        );
    }

    /**
     * Gets the legacy array
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'handler' => $this->handler,
            'route' => $this->route,
        ];
    }
}
