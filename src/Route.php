<?php
declare(strict_types=1);

namespace FastRoute;

use function preg_match;

class Route
{
    public string $httpMethod;

    public string $regex;

    /** @var mixed[] */
    public array $variables;

    /** @var mixed */
    public $handler;

    /** @param array{httpMethod: string, handler: mixed, regex: string, variables: array<string, string>} $state */
    public static function __set_state(array $state): self
    {
        return new self($state['httpMethod'], $state['handler'], $state['regex'], $state['variables']);
    }

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
