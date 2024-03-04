<?php
declare(strict_types=1);

namespace FastRoute;

use LogicException;

use function sprintf;
use function var_export;

/** @final */
class BadRouteException extends LogicException implements Exception
{
    public static function alreadyRegistered(string $route, string $method): self
    {
        return new self(sprintf('Cannot register two routes matching "%s" for method "%s"', $route, $method));
    }

    public static function namedRouteAlreadyDefined(string $name): self
    {
        return new self(sprintf('Cannot register two routes under the name "%s"', $name));
    }

    public static function invalidRouteName(mixed $name): self
    {
        return new self(sprintf('Route name must be a non-empty string, "%s" given', var_export($name, true)));
    }

    public static function shadowedByVariableRoute(string $route, string $shadowedRegex, string $method): self
    {
        return new self(
            sprintf(
                'Static route "%s" is shadowed by previously defined variable route "%s" for method "%s"',
                $route,
                $shadowedRegex,
                $method,
            ),
        );
    }

    public static function placeholderAlreadyDefined(string $name): self
    {
        return new self(sprintf('Cannot use the same placeholder "%s" twice', $name));
    }

    public static function variableWithCaptureGroup(string $regexPart, string $name): self
    {
        return new self(sprintf('Regex "%s" for parameter "%s" contains a capturing group', $regexPart, $name));
    }
}
