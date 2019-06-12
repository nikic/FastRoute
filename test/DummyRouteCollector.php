<?php
declare(strict_types=1);

namespace FastRoute\Test;

use FastRoute\RouteCollector;

class DummyRouteCollector extends RouteCollector
{
    /** @var mixed[] */
    public $routes = [];

    public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function addRoute($httpMethod, string $route, $handler): void
    {
        $route = $this->currentGroupPrefix . $route;
        $this->routes[] = [$httpMethod, $route, $handler];
    }
}
