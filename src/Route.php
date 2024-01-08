<?php
declare(strict_types=1);

namespace FastRoute;

use function is_string;
use function preg_match;
use function preg_quote;

/** @phpstan-import-type ExtraParameters from DataGenerator */
class Route
{
    /**
     * @param array<string, string> $variables
     * @param ExtraParameters       $extraParameters
     */
    public function __construct(
        public readonly string $httpMethod,
        public readonly mixed $handler,
        public readonly string $regex,
        public readonly array $variables,
        public readonly array $extraParameters,
    ) {
    }

    /**
     * @param array<string|array{0: string, 1:string}> $routeData
     * @param ExtraParameters                          $extraParameters
     */
    public static function fromParsedRoute(string $httpMethod, array $routeData, mixed $handler, array $extraParameters = []): self
    {
        [$regex, $variables] = self::extractRegex($routeData);

        return new self(
            $httpMethod,
            $handler,
            $regex,
            $variables,
            $extraParameters,
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
     * Tests whether this route matches the given string.
     */
    public function matches(string $str): bool
    {
        $regex = '~^' . $this->regex . '$~';

        return (bool) preg_match($regex, $str);
    }
}
