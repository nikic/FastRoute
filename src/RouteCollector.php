<?php

namespace FastRoute;

class RouteCollector {
    private $routeParser;
    private $dataGenerator;
    public $currentRouteGroup;

    /**
     * Constructs a route collector.
     *
     * @param RouteParser   $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator) {
        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed  $handler
     */
    public function addRoute($httpMethod, $route, $handler) {
        $route = $this->prependRouteWithRouteGroup($route);
        $routeDatas = $this->routeParser->parse($route);
        foreach ((array) $httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, $handler);
            }
        }
    }

    /**
     * Prepends the route string with the route string specified in the route group.
     *
     * @param string $route
     * @return string
     */
    protected function prependRouteWithRouteGroup($route)
    {
        return $this->prependRouteWithGroupsRoute($route, $this->currentRouteGroup);
    }


    /**
     * Prepends the provided route with the route inside the provided route group.
     *
     * @param string $route
     * @param null|\stdClass $group
     * @return string
     */
    private function prependRouteWithGroupsRoute($route, $group)
    {
        if (is_object($group)) {
            $route = "{$group->route}/" . ltrim($route, '/');
        }
        return $route;
    }

    /**
     * Creates a new route group and returns it.
     * If a previous group was passed it, it will prepend the groups route with the previous groups route.
     *
     * @param string $route
     * @param callable $callback
     * @param null|\stdClass $previousGroup
     * @return \stdClass
     */
    private function createGroup($route, callable $callback, $previousGroup) {
        $route = $this->prependRouteWithGroupsRoute($route, $previousGroup);
        $group = new \stdClass();
        $group->route = $route;
        $group->callback = $callback;
        $group->groups = [];
        return $group;
    }

    /**
     * Sets up a route group with a callback to allow you to create routes inside that group.
     *
     * @param string $route
     * @param callable $callback
     */
    public function addGroup($route, callable $callback)
    {
        $route = rtrim($route, '/');
        $previousGroup = $this->currentRouteGroup;
        $this->currentRouteGroup = $this->createGroup($route, $callback, $previousGroup);
        $callback($this);
        $this->currentRouteGroup = $previousGroup;
    }
    
    /**
     * Adds a GET route to the collection
     * 
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function get($route, $handler) {
        $this->addRoute('GET', $route, $handler);
    }
    
    /**
     * Adds a POST route to the collection
     * 
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function post($route, $handler) {
        $this->addRoute('POST', $route, $handler);
    }
    
    /**
     * Adds a PUT route to the collection
     * 
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function put($route, $handler) {
        $this->addRoute('PUT', $route, $handler);
    }
    
    /**
     * Adds a DELETE route to the collection
     * 
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function delete($route, $handler) {
        $this->addRoute('DELETE', $route, $handler);
    }
    
    /**
     * Adds a PATCH route to the collection
     * 
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function patch($route, $handler) {
        $this->addRoute('PATCH', $route, $handler);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function head($route, $handler) {
        $this->addRoute('HEAD', $route, $handler);
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array
     */
    public function getData() {
        return $this->dataGenerator->getData();
    }
}
