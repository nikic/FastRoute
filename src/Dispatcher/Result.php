<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

final class Result
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    public int $status;

    /** @var mixed */
    public $handler;

    /** @var array<string, mixed> */
    public array $variables = [];

    /** @var string[] */
    public array $allowedMethods = [];

    private function __construct()
    {
    }

    /**
     * @param mixed                $handler
     * @param array<string, mixed> $variables
     */
    public static function found($handler, array $variables = []): Result
    {
        $result = new self();
        $result->status = self::FOUND;
        $result->handler = $handler;
        $result->variables = $variables;

        return $result;
    }

    public static function notFound(): Result
    {
        $self = new self();
        $self->status = self::NOT_FOUND;

        return $self;
    }

    /** @param string[] $allowedMethods */
    public static function methodNotAllowed(array $allowedMethods): Result
    {
        $self = new self();
        $self->status = self::METHOD_NOT_ALLOWED;
        $self->allowedMethods = $allowedMethods;

        return $self;
    }
}
