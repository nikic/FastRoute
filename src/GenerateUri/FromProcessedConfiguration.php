<?php
declare(strict_types=1);

namespace FastRoute\GenerateUri;

use FastRoute\GenerateUri;
use FastRoute\RouteParser;

use function array_key_exists;
use function array_keys;
use function assert;
use function count;
use function is_string;
use function preg_match;

/**
 * @phpstan-import-type RoutesForUriGeneration from GenerateUri
 * @phpstan-import-type UriSubstitutions from GenerateUri
 * @phpstan-import-type ParsedRoute from RouteParser
 */
final class FromProcessedConfiguration implements GenerateUri
{
    /** @param RoutesForUriGeneration $processedConfiguration */
    public function __construct(private readonly array $processedConfiguration)
    {
    }

    /** @inheritDoc */
    public function forRoute(string $name, array $substitutions = []): GeneratedUri
    {
        if (! array_key_exists($name, $this->processedConfiguration)) {
            throw UriCouldNotBeGenerated::routeIsUndefined($name);
        }

        $missingParameters = [];

        foreach ($this->processedConfiguration[$name] as $parsedRoute) {
            $missingParameters = $this->missingParameters($parsedRoute, $substitutions);

            // Only attempt to generate the path if we have the necessary info
            if (count($missingParameters) === 0) {
                return $this->generatePath($name, $parsedRoute, $substitutions);
            }
        }

        assert(count($missingParameters) > 0);

        throw UriCouldNotBeGenerated::insufficientParameters(
            $name,
            $missingParameters,
            array_keys($substitutions),
        );
    }

    /**
     * Returns the expected parameters that were not passed as substitutions
     *
     * @param ParsedRoute      $parts
     * @param UriSubstitutions $substitutions
     *
     * @return list<string>
     */
    private function missingParameters(array $parts, array $substitutions): array
    {
        $missingParameters = [];

        foreach ($parts as $part) {
            if (is_string($part) || array_key_exists($part[0], $substitutions)) {
                continue;
            }

            $missingParameters[] = $part[0];
        }

        return $missingParameters;
    }

    /**
     * @param ParsedRoute      $parsedRoute
     * @param UriSubstitutions $substitutions
     */
    private function generatePath(string $route, array $parsedRoute, array $substitutions): GeneratedUri
    {
        $path = '';

        foreach ($parsedRoute as $part) {
            if (is_string($part)) {
                $path .= $part;

                continue;
            }

            [$parameterName, $regex] = $part;

            if (preg_match('~^' . $regex . '$~u', $substitutions[$parameterName]) !== 1) {
                throw UriCouldNotBeGenerated::parameterDoesNotMatchThePattern($route, $parameterName, $regex);
            }

            $path .= $substitutions[$parameterName];
            unset($substitutions[$parameterName]);
        }

        assert($path !== '');

        return new GeneratedUri($path, $substitutions);
    }
}
