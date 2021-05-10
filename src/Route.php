<?php
declare(strict_types=1);

namespace FastRoute;

use function preg_match;

class Route
{
    public string $httpMethod;

    public string $regex;

    /** @var array<string, string> */
    public array $variables;

    /** @var mixed */
    public $handler;

    /** @param array{httpMethod: string, handler: mixed, regex: string, variables: array<string, string>} $state */
    public static function __set_state(array $state): self
    {
        return new self($state['httpMethod'], $state['handler'], $state['regex'], $state['variables']);
    }

    /**
     * @param mixed                 $handler
     * @param array<string, string> $variables
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
     *
     * @param string $string URI string to match
     */
    public function matches(string $string): bool
    {
        $regex = '~^' . $this->regex . '$~';

        return (bool) preg_match($regex, $string);
    }
}
