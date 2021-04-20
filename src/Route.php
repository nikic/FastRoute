<?php
declare(strict_types=1);

namespace FastRoute;

use function preg_match;

class Route implements RouteInterface
{
    public string $httpMethod;

    public string $regex;

    /** @var mixed[] */
    public array $variables;

    /** @var mixed */
    public $handler;

    /** @var bool */
    public $isStatic = false;

    /**
     * @param mixed   $handler
     * @param mixed[] $variables
     */
    public function __construct(string $httpMethod, $handler, string $regex, array $variables, bool $isStatic = false)
    {
        $this->httpMethod = $httpMethod;
        $this->handler = $handler;
        $this->regex = $regex;
        $this->variables = $variables;
        $this->isStatic = $isStatic;
    }

    /**
     * Tests whether this route matches the given string.
     *
     * @param string $string URI string to match
     */
    public function matches(string $string): bool
    {
        if ($this->isStatic) {
            return $string === $this->regex;
        }

        $regex = '~^' . $this->regex . '$~';

        return (bool) preg_match($regex, $string);
    }

    /**
     * @return mixed
     */
    public function handler()
    {
        return $this->handler;
    }

    public function regex(): string
    {
        return $this->regex;
    }

    /**
     * @return mixed[]
     */
    public function variables(): array
    {
        return $this->variables;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }
}
