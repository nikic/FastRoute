<?php
declare(strict_types=1);

namespace FastRoute;

/**
 * @phpstan-import-type RouteData from DataGenerator
 * @phpstan-import-type ExtraParameters from DataGenerator
 */
interface ConfigureRoutes
{
    /**
     * Registers a new route.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param ExtraParameters $extraParameters
     */
    public function addRoute(string|array $httpMethod, string $route, mixed $handler, array $extraParameters = []): void;

    /**
     * Create a route group with a common prefix.
     *
     * All routes created by the passed callback will have the given group prefix prepended.
     */
    public function addGroup(string $prefix, callable $callback): void;

    /**
     * Adds a fallback route to the collection
     *
     * This is simply an alias of $this->addRoute('*', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function any(string $route, mixed $handler, array $extraParameters = []): void;

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function get(string $route, mixed $handler, array $extraParameters = []): void;

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function post(string $route, mixed $handler, array $extraParameters = []): void;

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function put(string $route, mixed $handler, array $extraParameters = []): void;

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function delete(string $route, mixed $handler, array $extraParameters = []): void;

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function patch(string $route, mixed $handler, array $extraParameters = []): void;

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function head(string $route, mixed $handler, array $extraParameters = []): void;

    /**
     * Adds an OPTIONS route to the collection
     *
     * This is simply an alias of $this->addRoute('OPTIONS', $route, $handler)
     *
     * @param ExtraParameters $extraParameters
     */
    public function options(string $route, mixed $handler, array $extraParameters = []): void;

    /**
     * Returns the processed aggregated route data.
     *
     * @return RouteData
     */
    public function processedRoutes(): array;
}
