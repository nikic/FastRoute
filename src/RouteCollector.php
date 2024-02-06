<?php
declare(strict_types=1);

namespace FastRoute;

/**
 * @phpstan-import-type RouteData from DataGenerator
 * @phpstan-import-type ExtraParameters from DataGenerator
 * @final
 */
class RouteCollector implements ConfigureRoutes
{
    protected string $currentGroupPrefix = '';

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

        $extraParameters = ['_route' => $route] + $extraParameters;

        foreach ((array) $httpMethod as $method) {
            foreach ($parsedRoutes as $parsedRoute) {
                $this->dataGenerator->addRoute($method, $parsedRoute, $handler, $extraParameters);
            }
        }
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
        return $this->dataGenerator->getData();
    }

    /**
     * @deprecated
     *
     * @see ConfigureRoutes::processedRoutes()
     *
     * @return RouteData
     */
    public function getData(): array
    {
        return $this->processedRoutes();
    }
}
