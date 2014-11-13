<?php

namespace FastRoute\Dispatcher;

use FastRoute\RouteCollector;

abstract class DispatcherTest extends \PHPUnit_Framework_TestCase {

    /**
     * Delegate dispatcher selection to child test classes
     */
    abstract protected function getDispatcherClass();

    /**
     * Delegate dataGenerator selection to child test classes
     */
    abstract protected function getDataGeneratorClass();

    /**
     * Set appropriate options for the specific Dispatcher class we're testing
     */
    private function generateDispatcherOptions() {
        return array(
            'dataGenerator' => $this->getDataGeneratorClass(),
            'dispatcher' => $this->getDispatcherClass()
        );
    }

    /**
     * @dataProvider provideFoundDispatchCases
     */
    public function testFoundDispatches($method, $uri, $callback, $handler, $argDict) {
        $dispatcher = \FastRoute\simpleDispatcher($callback, $this->generateDispatcherOptions());
        list($routedStatus, $routedTo, $routedArgs) = $dispatcher->dispatch($method, $uri);
        $this->assertSame($dispatcher::FOUND, $routedStatus);
        $this->assertSame($handler, $routedTo);
        $this->assertSame($argDict, $routedArgs);
    }

    /**
     * @dataProvider provideNotFoundDispatchCases
     */
    public function testNotFoundDispatches($method, $uri, $callback) {
        $dispatcher = \FastRoute\simpleDispatcher($callback, $this->generateDispatcherOptions());
        $this->assertFalse(isset($routeInfo[1]),
            "NOT_FOUND result must only contain a single element in the returned info array"
        );
        list($routedStatus) = $dispatcher->dispatch($method, $uri);
        $this->assertSame($dispatcher::NOT_FOUND, $routedStatus);
    }

    /**
     * @dataProvider provideMethodNotAllowedDispatchCases
     */
    public function testMethodNotAllowedDispatches($method, $uri, $callback, $availableMethods) {
        $dispatcher = \FastRoute\simpleDispatcher($callback, $this->generateDispatcherOptions());
        $routeInfo = $dispatcher->dispatch($method, $uri);
        $this->assertTrue(isset($routeInfo[1]),
            "METHOD_NOT_ALLOWED result must return an array of allowed methods at index 1"
        );

        list($routedStatus, $methodArray) = $dispatcher->dispatch($method, $uri);
        $this->assertSame($dispatcher::METHOD_NOT_ALLOWED, $routedStatus);
        $this->assertSame($availableMethods, $methodArray);
    }

    /**
     * @expectedException \FastRoute\BadRouteException
     * @expectedExceptionMessage Cannot use the same placeholder "test" twice
     */
    public function testDuplicateVariableNameError() {
        \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            $r->addRoute('GET', '/foo/{test}/{test:\d+}', 'handler0');
        }, $this->generateDispatcherOptions());
    }

    /**
     * @expectedException \FastRoute\BadRouteException
     * @expectedExceptionMessage Cannot register two routes matching "/user/([^/]+)" for method "GET"
     */
    public function testDuplicateVariableRoute() {
        \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            $r->addRoute('GET', '/user/{id}', 'handler0'); // oops, forgot \d+ restriction ;)
            $r->addRoute('GET', '/user/{name}', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    /**
     * @expectedException \FastRoute\BadRouteException
     * @expectedExceptionMessage Cannot register two routes matching "/user" for method "GET"
     */
    public function testDuplicateStaticRoute() {
        \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            $r->addRoute('GET', '/user', 'handler0');
            $r->addRoute('GET', '/user', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    /**
     * @expectedException \FastRoute\BadRouteException
     * @expectedExceptionMessage Static route "/user/nikic" is shadowed by previously defined variable route "/user/([^/]+)" for method "GET"
     */
    public function testShadowedStaticRoute() {
        \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/nikic', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    public function provideFoundDispatchCases() {
        $cases = array();

        // 0 -------------------------------------------------------------------------------------->

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        $method = 'GET';
        $uri = '/resource/123/456';
        $handler = 'handler0';
        $argDict = array();

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // 1 -------------------------------------------------------------------------------------->

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/handler0', 'handler0');
            $r->addRoute('GET', '/handler1', 'handler1');
            $r->addRoute('GET', '/handler2', 'handler2');
        };

        $method = 'GET';
        $uri = '/handler2';
        $handler = 'handler2';
        $argDict = array();

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // 2 -------------------------------------------------------------------------------------->

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/user/{name}', 'handler2');
        };

        $method = 'GET';
        $uri = '/user/rdlowrey';
        $handler = 'handler2';
        $argDict = array('name' => 'rdlowrey');

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // 3 -------------------------------------------------------------------------------------->

        // reuse $callback from #2

        $method = 'GET';
        $uri = '/user/12345';
        $handler = 'handler1';
        $argDict = array('id' => '12345');

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // 4 -------------------------------------------------------------------------------------->

        // reuse $callback from #3

        $method = 'GET';
        $uri = '/user/NaN';
        $handler = 'handler2';
        $argDict = array('name' => 'NaN');

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // 5 -------------------------------------------------------------------------------------->

        // reuse $callback from #4

        $method = 'GET';
        $uri = '/user/rdlowrey/12345';
        $handler = 'handler0';
        $argDict = array('name' => 'rdlowrey', 'id' => '12345');

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // 6 -------------------------------------------------------------------------------------->

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/12345/extension', 'handler1');
            $r->addRoute('GET', '/user/{id:[0-9]+}.{extension}', 'handler2');

        };

        $method = 'GET';
        $uri = '/user/12345.svg';
        $handler = 'handler2';
        $argDict = array('id' => '12345', 'extension' => 'svg');

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // 7 ----- Test GET method fallback on HEAD route miss ------------------------------------>

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/static0', 'handler2');
            $r->addRoute('GET', '/static1', 'handler3');
            $r->addRoute('HEAD', '/static1', 'handler4');
        };

        $method = 'HEAD';
        $uri = '/user/rdlowrey';
        $handler = 'handler0';
        $argDict = array('name' => 'rdlowrey');

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // 8 ----- Test GET method fallback on HEAD route miss ------------------------------------>

        // reuse $callback from #7

        $method = 'HEAD';
        $uri = '/user/rdlowrey/1234';
        $handler = 'handler1';
        $argDict = array('name' => 'rdlowrey', 'id' => '1234');

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // 9 ----- Test GET method fallback on HEAD route miss ------------------------------------>

        // reuse $callback from #8

        $method = 'HEAD';
        $uri = '/static0';
        $handler = 'handler2';
        $argDict = array();

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // 10 ---- Test existing HEAD route used if available (no fallback) ----------------------->

        // reuse $callback from #9

        $method = 'HEAD';
        $uri = '/static1';
        $handler = 'handler4';
        $argDict = array();

        $cases[] = array($method, $uri, $callback, $handler, $argDict);

        // x -------------------------------------------------------------------------------------->

        return $cases;
    }

    public function provideNotFoundDispatchCases() {
        $cases = array();

        // 0 -------------------------------------------------------------------------------------->

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        $method = 'GET';
        $uri = '/not-found';

        $cases[] = array($method, $uri, $callback);

        // 1 -------------------------------------------------------------------------------------->

        // reuse callback from #0
        $method = 'POST';
        $uri = '/not-found';

        $cases[] = array($method, $uri, $callback);

        // 2 -------------------------------------------------------------------------------------->

        // reuse callback from #1
        $method = 'PUT';
        $uri = '/not-found';

        $cases[] = array($method, $uri, $callback);

        // 3 -------------------------------------------------------------------------------------->

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/handler0', 'handler0');
            $r->addRoute('GET', '/handler1', 'handler1');
            $r->addRoute('GET', '/handler2', 'handler2');
        };

        $method = 'GET';
        $uri = '/not-found';

        $cases[] = array($method, $uri, $callback);

        // 4 -------------------------------------------------------------------------------------->

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/user/{name}', 'handler2');
        };

        $method = 'GET';
        $uri = '/not-found';

        $cases[] = array($method, $uri, $callback);

        // 5 -------------------------------------------------------------------------------------->

        // reuse callback from #4
        $method = 'GET';
        $uri = '/user/rdlowrey/12345/not-found';

        $cases[] = array($method, $uri, $callback);

        // x -------------------------------------------------------------------------------------->

        return $cases;
    }

    public function provideMethodNotAllowedDispatchCases() {
        $cases = array();

        // 0 -------------------------------------------------------------------------------------->

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        $method = 'POST';
        $uri = '/resource/123/456';
        $allowedMethods = array('GET');

        $cases[] = array($method, $uri, $callback, $allowedMethods);

        // 1 -------------------------------------------------------------------------------------->

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
            $r->addRoute('POST', '/resource/123/456', 'handler1');
            $r->addRoute('PUT', '/resource/123/456', 'handler2');
        };

        $method = 'DELETE';
        $uri = '/resource/123/456';
        $allowedMethods = array('GET', 'POST', 'PUT');

        $cases[] = array($method, $uri, $callback, $allowedMethods);

        // 2 -------------------------------------------------------------------------------------->

        $callback = function(RouteCollector $r) {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('POST', '/user/{name}/{id:[0-9]+}', 'handler1');
            $r->addRoute('PUT', '/user/{name}/{id:[0-9]+}', 'handler2');
            $r->addRoute('PATCH', '/user/{name}/{id:[0-9]+}', 'handler3');
        };

        $method = 'DELETE';
        $uri = '/user/rdlowrey/42';
        $allowedMethods = array('GET', 'POST', 'PUT', 'PATCH');

        $cases[] = array($method, $uri, $callback, $allowedMethods);

        // x -------------------------------------------------------------------------------------->

        return $cases;
    }

}
