<?php
namespace FastRoute\Test;

use FastRoute\RouteCollector;

class DummyRouteCollector extends RouteCollector
{
    public $routes = [];

    public function __construct()
    {
    }

    public function addRoute($method, string $route, $handler):void
    {
        $route = $this->currentGroupPrefix . $route;
        $this->routes[] = [$method, $route, $handler];
    }
}
