<?php
declare(strict_types=1);

namespace FastRoute\Test;

use FastRoute\RouteCollection;

class DummyRouteCollection extends RouteCollection
{
    /** @var mixed[] */
    public array $routes = [];

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
