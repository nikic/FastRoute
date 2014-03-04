<?php

namespace FastRoute;

class RouteCollector {
    /**
     * @var RouteParser
     */
    private $routeParser;

    /**
     * @var DataGenerator
     */
    private $dataGenerator;

    /**
     * Constructor
     * 
     * @param RouteParser   $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator) {
        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
    }

    /**
     * Add a route to the collection
     * 
     * @param string   $httpMethod
     * @param string   $route
     * @param callable $handler
     */
    public function addRoute($httpMethod, $route, $handler) {
        $routeData = $this->routeParser->parse($route);
        $this->dataGenerator->addRoute($httpMethod, $routeData, $handler);
    }

    /**
     * Get collected route data
     * 
     * @return array
     */
    public function getData() {
        return $this->dataGenerator->getData();
    }
}
