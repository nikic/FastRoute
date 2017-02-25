<?php

namespace FastRoute;

class RouteCollectorTest extends \PHPUnit_Framework_TestCase {
    public function testShortcuts() {
        $r = new DummyRouteCollector();

        $r->delete('/delete', 'delete', ['dataDel']);
        $r->get('/get', 'get');
        $r->head('/head', 'head', ['dataHead1', 'dataHead2']);
        $r->patch('/patch', 'patch');
        $r->post('/post', 'post', []);
        $r->put('/put', 'put');

        $expected = [
            ['DELETE', '/delete', 'delete', ['dataDel']],
            ['GET', '/get', 'get', []],
            ['HEAD', '/head', 'head', ['dataHead1', 'dataHead2']],
            ['PATCH', '/patch', 'patch', []],
            ['POST', '/post', 'post', []],
            ['PUT', '/put', 'put', []],
        ];

        $this->assertSame($expected, $r->routes);
    }

    public function testGroups() {
        $r = new DummyRouteCollector();

        $r->delete('/delete', 'delete');
        $r->get('/get', 'get');
        $r->head('/head', 'head');
        $r->patch('/patch', 'patch');
        $r->post('/post', 'post');
        $r->put('/put', 'put');

        $r->addGroup('/group-one', function (DummyRouteCollector $r) {
            $r->delete('/delete', 'delete');
            $r->get('/get', 'get');
            $r->head('/head', 'head');
            $r->patch('/patch', 'patch');
            $r->post('/post', 'post');
            $r->put('/put', 'put', ['put']);

            $r->addGroup('/group-two', function (DummyRouteCollector $r) {
                $r->delete('/delete', 'delete');
                $r->get('/get', 'get');
                $r->head('/head', 'head');
                $r->patch('/patch', 'patch');
                $r->post('/post', 'post');
                $r->put('/put', 'put', ['put']);
            }, ['g2']);
        }, ['g1']);

        $r->addGroup('/admin', function (DummyRouteCollector $r) {
            $r->get('-some-info', 'admin-some-info');
        });
        $r->addGroup('/admin-', function (DummyRouteCollector $r) {
            $r->get('more-info', 'admin-more-info');
        });

        $expected = [
            ['DELETE', '/delete', 'delete', []],
            ['GET', '/get', 'get', []],
            ['HEAD', '/head', 'head', []],
            ['PATCH', '/patch', 'patch', []],
            ['POST', '/post', 'post', []],
            ['PUT', '/put', 'put', []],
            ['DELETE', '/group-one/delete', 'delete', ['g1']],
            ['GET', '/group-one/get', 'get', ['g1']],
            ['HEAD', '/group-one/head', 'head', ['g1']],
            ['PATCH', '/group-one/patch', 'patch', ['g1']],
            ['POST', '/group-one/post', 'post', ['g1']],
            ['PUT', '/group-one/put', 'put', ['g1', 'put']],
            ['DELETE', '/group-one/group-two/delete', 'delete', ['g1', 'g2']],
            ['GET', '/group-one/group-two/get', 'get', ['g1', 'g2']],
            ['HEAD', '/group-one/group-two/head', 'head', ['g1', 'g2']],
            ['PATCH', '/group-one/group-two/patch', 'patch', ['g1', 'g2']],
            ['POST', '/group-one/group-two/post', 'post', ['g1', 'g2']],
            ['PUT', '/group-one/group-two/put', 'put', ['g1', 'g2', 'put']],
            ['GET', '/admin-some-info', 'admin-some-info', []],
            ['GET', '/admin-more-info', 'admin-more-info', []],
        ];

        $this->assertSame($expected, $r->routes);
    }

    public function testGroupDataOverride()
    {
        $r = new DummyRouteCollector();

        $r->addGroup('/g1', function(DummyRouteCollector $r) {
            $r->get('/get', 'get');
            $r->put('/put', 'put', ['data' => 'put']);
        }, ['data' => 'g1']);

        $r->addGroup('/g2', function(DummyRouteCollector $r) {
            $r->get('/get', 'get', ['dt' => ['dt' => 'bar'], 'foo' => 'bar']);
            $r->put('/put', 'put');
        }, ['dt' => ['dt' => 'foo']]);

        $r->addGroup('/g3', function(DummyRouteCollector $r) {
            $r->get('/get', 'get', ['dt' => ['bar']]);
        }, ['dt' => ['foo']]);

        $expected = [
            ['GET', '/g1/get', 'get', ['data' => 'g1']],
            ['PUT', '/g1/put', 'put', ['data' => 'put']],
            ['GET', '/g2/get', 'get', ['dt' => ['dt' => 'bar'], 'foo' => 'bar']],
            ['PUT', '/g2/put', 'put', ['dt' => ['dt' => 'foo']]],
            ['GET', '/g3/get', 'get', ['dt' => ['bar']]]
        ];

        $this->assertSame($expected, $r->routes);
    }
}

class DummyRouteCollector extends RouteCollector {
    public $routes = [];
    public function __construct() {}
    public function addRoute($method, $route, $handler, array $data = []) {
        $route = $this->currentGroupPrefix . $route;
        $data = array_merge($this->currentGroupData, $data);
        $this->routes[] = [$method, $route, $handler, $data];
    }
}