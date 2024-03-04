<?php
declare(strict_types=1);

namespace FastRoute\GenerateUri;

use FastRoute\Exception;
use LogicException;

use function count;
use function implode;
use function sprintf;

final class UriCouldNotBeGenerated extends LogicException implements Exception
{
    public static function routeIsUndefined(string $name): self
    {
        return new self('There is no route with name "' . $name . '" defined');
    }

    public static function parameterDoesNotMatchThePattern(
        string $route,
        string $parameter,
        string $expectedPattern,
    ): self {
        return new self(
            sprintf(
                'Route "%s" expects the parameter [%s] to match the regex `%s`',
                $route,
                $parameter,
                $expectedPattern,
            ),
        );
    }

    /**
     * @param non-empty-list<string> $missingParameters
     * @param list<string>           $givenParameters
     */
    public static function insufficientParameters(
        string $route,
        array $missingParameters,
        array $givenParameters,
    ): self {
        return new self(
            sprintf(
                'Route "%s" expects at least parameter values for [%s], but received %s',
                $route,
                implode(',', $missingParameters),
                count($givenParameters) === 0 ? 'none' : '[' . implode(',', $givenParameters) . ']',
            ),
        );
    }
}
