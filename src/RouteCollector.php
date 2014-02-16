<?php

namespace FastRoute;

class RouteCollector {
    private $routeParser;
    private $dataGenerator;

    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator) {
        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
    }

    function addRoute($httpMethod, $route, $handler) {
        $routeData = $this->routeParser->parse($route);
        $this->dataGenerator->addRoute($httpMethod, $routeData, $handler);
    }

    public function getData() {
        return $this->dataGenerator->getData();
    }
}
