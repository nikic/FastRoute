<?php

namespace FastRoute;

class RouteCollector {
    protected $routeParser;
    protected $dataGenerator;
    protected $currentGroupPrefix;
    protected $currentGroupData = [];

    /**
     * Constructs a route collector.
     *
     * @param RouteParser   $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator) {
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
     * @param string $route
     * @param mixed  $handler
     */
    public function addRoute($httpMethod, $route, $handler, array $data = []) {
        $route = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);
        $data = array_merge($data, $this->currentGroupData);
        foreach ((array) $httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, $handler, $data);
            }
        }
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     *
     * @param string $prefix
     * @param callable $callback
     */
    public function addGroup($prefix, callable $callback, array $data = []) {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $previousGroupData = $this->currentGroupData;
        $this->currentGroupData = $previousGroupData + $data;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
        $this->currentGroupData = $previousGroupData;
    }
    
    /**
     * Adds a GET route to the collection
     * 
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function get($route, $handler, array $data = []) {
        $this->addRoute('GET', $route, $handler, $data);
    }
    
    /**
     * Adds a POST route to the collection
     * 
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function post($route, $handler, array $data = []) {
        $this->addRoute('POST', $route, $handler, $data);
    }
    
    /**
     * Adds a PUT route to the collection
     * 
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function put($route, $handler, array $data = []) {
        $this->addRoute('PUT', $route, $handler, $data);
    }
    
    /**
     * Adds a DELETE route to the collection
     * 
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function delete($route, $handler, array $data = []) {
        $this->addRoute('DELETE', $route, $handler, $data);
    }
    
    /**
     * Adds a PATCH route to the collection
     * 
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function patch($route, $handler, array $data = []) {
        $this->addRoute('PATCH', $route, $handler, $data);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function head($route, $handler, array $data = []) {
        $this->addRoute('HEAD', $route, $handler, $data);
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
