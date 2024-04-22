<?php
declare(strict_types=1);

namespace FastRoute;

use function array_key_exists;

final class RouteCollectorImmutable extends RouteCollectorAbstract
{
    /** @inheritDoc */
    public function addRoute(string|array $httpMethod, string $route, mixed $handler, array $extraParameters = []): static
    {
        $clone = clone $this;
        $clone->dataGenerator = clone $clone->dataGenerator;

        $route = $clone->currentGroupPrefix . $route;
        $parsedRoutes = $clone->routeParser->parse($route);

        $extraParameters = [self::ROUTE_REGEX => $route] + $extraParameters;

        foreach ((array) $httpMethod as $method) {
            foreach ($parsedRoutes as $parsedRoute) {
                $clone->dataGenerator->addRoute($method, $parsedRoute, $handler, $extraParameters);
            }
        }

        if (array_key_exists(self::ROUTE_NAME, $extraParameters)) {
            $clone->registerNamedRoute($extraParameters[self::ROUTE_NAME], $parsedRoutes);
        }

        return $clone;
    }

    /** @inheritDoc */
    public function addGroup(string $prefix, callable $callback): static
    {
        $clone = clone $this;

        $previousGroupPrefix = $clone->currentGroupPrefix;
        $clone->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $clone = $callback($clone);
        $clone->currentGroupPrefix = $previousGroupPrefix;

        return $clone;
    }
}
