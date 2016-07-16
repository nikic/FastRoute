<?php

namespace FastRoute;

class RouteCollectorTest extends \PHPUnit_Framework_TestCase {
    public function testShortcuts() {
        $r = new DummyRouteCollector();

        $r->delete('/delete', 'delete');
        $r->get('/get', 'get');
        $r->head('/head', 'head');
        $r->patch('/patch', 'patch');
        $r->post('/post', 'post');
        $r->put('/put', 'put');

        $expected = [
            ['DELETE', '/delete', 'delete'],
            ['GET', '/get', 'get'],
            ['HEAD', '/head', 'head'],
            ['PATCH', '/patch', 'patch'],
            ['POST', '/post', 'post'],
            ['PUT', '/put', 'put'],
        ];

        $this->assertSame($expected, $r->routes);
    }
}

class DummyRouteCollector extends RouteCollector {
    public $routes = [];
    public function __construct() {}
    public function addRoute($method, $route, $handler) {
        $this->routes[] = [$method, $route, $handler];
    }
}