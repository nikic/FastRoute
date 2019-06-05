<?php

namespace FastRoute;

class RouteCollector
{
    /** @var RouteParser */
    protected $routeParser;

    /** @var DataGenerator */
    protected $dataGenerator;

    /** @var string */
    protected $currentGroupPrefix;

    /**
     * Constructs a route collector.
     *
     * @param RouteParser   $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator)
    {
        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
        $this->currentGroupPrefix = '';
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string          $route
     * @param mixed           $handler
     *
     * @return \FastRoute\RouteCollector
     */
    public function addRoute($httpMethod, string $route, $handler):RouteCollector
    {
        $route = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);
        foreach ((array) $httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, $handler);
            }
        }

        return $this;
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     *
     * @param string   $prefix
     * @param callable $callback
     *
     * @return \FastRoute\RouteCollector
     */
    public function addGroup(string $prefix, callable $callback):RouteCollector
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;

        return $this;
    }

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     *
     * @return \FastRoute\RouteCollector
     */
    public function get(string $route, $handler):RouteCollector
    {
        $this->addRoute('GET', $route, $handler);

        return $this;
    }

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     *
     * @return \FastRoute\RouteCollector
     */
    public function post(string $route, $handler):RouteCollector
    {
        $this->addRoute('POST', $route, $handler);

        return $this;
    }

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     *
     * @return \FastRoute\RouteCollector
     */
    public function put(string $route, $handler):RouteCollector
    {
        $this->addRoute('PUT', $route, $handler);

        return $this;
    }

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     *
     * @return \FastRoute\RouteCollector
     */
    public function delete(string $route, $handler):RouteCollector
    {
        $this->addRoute('DELETE', $route, $handler);

        return $this;
    }

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     *
     * @return \FastRoute\RouteCollector
     */
    public function patch(string $route, $handler):RouteCollector
    {
        $this->addRoute('PATCH', $route, $handler);

        return $this;
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     *
     * @return \FastRoute\RouteCollector
     */
    public function head(string $route, $handler):RouteCollector
    {
        $this->addRoute('HEAD', $route, $handler);

        return $this;
    }

    /**
     * Adds an OPTIONS route to the collection
     *
     * This is simply an alias of $this->addRoute('OPTIONS', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     *
     * @return \FastRoute\RouteCollector
     */
    public function options(string $route, $handler):RouteCollector
    {
        $this->addRoute('OPTIONS', $route, $handler);

        return $this;
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array
     */
    public function getData():array
    {
        return $this->dataGenerator->getData();
    }
}
