<?php

namespace FastRoute;

class RouteCollector {
    private $routeParser;
    private $dataGenerator;

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
        $routeData = $this->routeParser->parse($route);
        foreach ((array) $httpMethod as $method) {
            $this->dataGenerator->addRoute($method, $routeData, $handler);
        }
    }

    /**
     * Adds a GET route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function get($route, $handler) {
        $this->addRoute('GET', $route, $handler);
    }

    /**
     * Adds a POST route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function post($route, $handler) {
        $this->addRoute('POST', $route, $handler);
    }

    /**
     * Adds a PUT route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function put($route, $handler) {
        $this->addRoute('PUT', $route, $handler);
    }

    /**
     * Adds a DELETE route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function delete($route, $handler) {
        $this->addRoute('DELETE', $route, $handler);
    }

    /**
     * Adds a HEAD route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function head($route, $handler) {
        $this->addRoute('HEAD', $route, $handler);
    }

    /**
     * Adds an ANY route to the collection.  This route matches all HTTP methods.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function any($route, $handler) {
        $this->addRoute('ANY', $route, $handler);
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
