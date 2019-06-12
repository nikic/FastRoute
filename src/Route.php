<?php
declare(strict_types=1);

namespace FastRoute;

use function preg_match;

class Route
{
    /** @var string */
    public $httpMethod;

    /** @var string */
    public $regex;

    /** @var mixed[] */
    public $variables;

    /** @var mixed */
    public $handler;

    /**
     * @param mixed   $handler
     * @param mixed[] $variables
     */
    public function __construct(string $httpMethod, $handler, string $regex, array $variables)
    {
        $this->httpMethod = $httpMethod;
        $this->handler = $handler;
        $this->regex = $regex;
        $this->variables = $variables;
    }

    /**
     * Tests whether this route matches the given string.
     */
    public function matches(string $str): bool
    {
        $regex = '~^' . $this->regex . '$~';

        return (bool) preg_match($regex, $str);
    }
}
