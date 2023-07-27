<?php
declare(strict_types=1);

namespace FastRoute;

use function preg_match;

class Route
{
    /** @param array<string, string> $variables */
    public function __construct(public string $httpMethod, public mixed $handler, public string $regex, public array $variables)
    {
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
