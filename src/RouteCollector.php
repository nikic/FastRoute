<?php
declare(strict_types=1);

namespace FastRoute;

use function array_key_exists;
use function array_reverse;
use function is_string;

/**
 * @phpstan-import-type ProcessedData from ConfigureRoutes
 * @phpstan-import-type ExtraParameters from DataGenerator
 * @phpstan-import-type RoutesForUriGeneration from GenerateUri
 * @phpstan-import-type ParsedRoutes from RouteParser
 * @final
 */
class RouteCollector implements ConfigureRoutes
{
    protected string $currentGroupPrefix = '';

    /** @var RoutesForUriGeneration */
    private array $namedRoutes = [];

    public function __construct(
        protected readonly RouteParser $routeParser,
        protected readonly DataGenerator $dataGenerator,
    ) {
    }

    /** @inheritDoc */
    public function addRoute(string|array $httpMethod, string $route, mixed $handler, array $extraParameters = []): void
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
    }

    /** @param ParsedRoutes $parsedRoutes */
    private function registerNamedRoute(mixed $name, array $parsedRoutes): void
    {
        if (! is_string($name) || $name === '') {
            throw BadRouteException::invalidRouteName($name);
        }

        if (array_key_exists($name, $this->namedRoutes)) {
            throw BadRouteException::namedRouteAlreadyDefined($name);
        }

        $this->namedRoutes[$name] = array_reverse($parsedRoutes);
    }

    public function addGroup(string $prefix, callable $callback): void
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
    }

    /** @inheritDoc */
    public function any(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('*', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function get(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('GET', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function post(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('POST', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function put(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('PUT', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function delete(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('DELETE', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function patch(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('PATCH', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function head(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('HEAD', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function options(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('OPTIONS', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function processedRoutes(): array
    {
        $data =  $this->dataGenerator->getData();
        $data[] = $this->namedRoutes;

        return $data;
    }

    /**
     * @deprecated
     *
     * @see ConfigureRoutes::processedRoutes()
     *
     * @return ProcessedData
     */
    public function getData(): array
    {
        return $this->processedRoutes();
    }
}
