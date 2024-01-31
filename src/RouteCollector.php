<?php
declare(strict_types=1);

namespace FastRoute;

/**
 * @phpstan-import-type RouteData from DataGenerator
 * @phpstan-import-type ExtraParameters from DataGenerator
 * @final
 */
class RouteCollector
{
    protected string $currentGroupPrefix = '';

    public function __construct(
        protected readonly RouteParser $routeParser,
        protected readonly DataGenerator $dataGenerator,
    ) {
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param ExtraParameters $extraParameters
     */
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

    /**
     * Create a route group with a common prefix.
     *
     * All routes created by the passed callback will have the given group prefix prepended.
     */
    public function addGroup(string $prefix, callable $callback): void
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
    }

    /**
     * Adds a fallback route to the collection
     *
     * This is simply an alias of $this->addRoute('*', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function any(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('*', $route, $handler, $extraParameters);
    }

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function get(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('GET', $route, $handler, $extraParameters);
    }

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function post(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('POST', $route, $handler, $extraParameters);
    }

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function put(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('PUT', $route, $handler, $extraParameters);
    }

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function delete(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('DELETE', $route, $handler, $extraParameters);
    }

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function patch(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('PATCH', $route, $handler, $extraParameters);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function head(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('HEAD', $route, $handler, $extraParameters);
    }

    /**
     * Adds an OPTIONS route to the collection
     *
     * This is simply an alias of $this->addRoute('OPTIONS', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function options(string $route, mixed $handler, array $extraParameters = []): void
    {
        $this->addRoute('OPTIONS', $route, $handler, $extraParameters);
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return RouteData
     */
    public function getData(): array
    {
        return $this->dataGenerator->getData();
    }
}
