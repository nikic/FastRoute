<?php
declare(strict_types=1);

namespace FastRoute;

use function array_key_exists;

final class RouteCollector extends RouteCollectorAbstract
{
    /** @inheritDoc */
    public function addRoute(string|array $httpMethod, string $route, mixed $handler, array $extraParameters = []): static
    {
        $route = $this->currentGroupPrefix . $route;
        $parsedRoutes = $this->routeParser->parse($route);

        $extraParameters = [self::ROUTE_REGEX => $route] + $extraParameters;

        foreach ((array) $httpMethod as $method) {
            foreach ($parsedRoutes as $parsedRoute) {
                $this->dataGenerator->addRoute($method, $parsedRoute, $handler, $extraParameters);
            }
        }

        if (array_key_exists(self::ROUTE_NAME, $extraParameters)) {
            $this->registerNamedRoute($extraParameters[self::ROUTE_NAME], $parsedRoutes);
        }

        return $this;
    }

    /** @inheritDoc */
    public function addGroup(string $prefix, callable $callback): static
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;

        return $this;
    }
}
