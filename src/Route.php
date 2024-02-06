<?php
declare(strict_types=1);

namespace FastRoute;

use function is_string;
use function preg_match;
use function preg_quote;

/**
 * @internal
 *
 * @phpstan-import-type ExtraParameters from DataGenerator
 * @phpstan-import-type ParsedRoute from RouteParser
 */
class Route
{
    public readonly string $regex;

    /** @var array<string, string> $variables */
    public readonly array $variables;

    /**
     * @param ParsedRoute     $routeData
     * @param ExtraParameters $extraParameters
     */
    public function __construct(
        public readonly string $httpMethod,
        array $routeData,
        public readonly mixed $handler,
        public readonly array $extraParameters,
    ) {
        [$this->regex, $this->variables] = self::extractRegex($routeData);
    }

    /**
     * @param ParsedRoute $routeData
     *
     * @return array{string, array<string, string>}
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
     * Tests whether this route matches the given string.
     */
    public function matches(string $str): bool
    {
        $regex = '~^' . $this->regex . '$~';

        return (bool) preg_match($regex, $str);
    }
}
