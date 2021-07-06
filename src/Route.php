<?php
declare(strict_types=1);

namespace FastRoute;

use function is_string;
use function preg_match;
use function preg_quote;

class Route
{
    public string $httpMethod;

    public string $regex;

    /** @var array<string, string> */
    public array $variables;

    /** @var mixed */
    public $handler;

    /**
     * @param array<string|array{0: string, 1:string}> $routeData
     * @param mixed                                    $handler
     */
    public static function fromParsedRoute(string $httpMethod, array $routeData, $handler): self
    {
        [$regex, $variables] = self::extractRegex($routeData);

        return new self(
            $httpMethod,
            $handler,
            $regex,
            $variables
        );
    }

    /**
     * @param array<string|array{0: string, 1:string}> $routeData
     *
     * @return array{0: string, 1: array<string, string>}
     */
    private static function extractRegex(array $routeData): array
    {
        $regex = '';
        $variables = [];

        foreach ($routeData as $part) {
            if (is_string($part)) {
                $regex .= preg_quote($part, '~');
                continue;
            }

            [$varName, $regexPart] = $part;

            $variables[$varName] = $varName;
            $regex .= '(' . $regexPart . ')';
        }

        return [$regex, $variables];
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
     */
    public function matches(string $str): bool
    {
        $regex = '~^' . $this->regex . '$~';

        return (bool) preg_match($regex, $str);
    }
}
