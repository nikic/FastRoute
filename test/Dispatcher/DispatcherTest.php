<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\BadRouteException;
use FastRoute\RouteCollector;
use PHPUnit\Framework\TestCase;
use function FastRoute\simpleDispatcher;

abstract class DispatcherTest extends TestCase
{
    /**
     * Delegate dispatcher selection to child test classes
     */
    abstract protected function getDispatcherClass(): string;

    /**
     * Delegate dataGenerator selection to child test classes
     */
    abstract protected function getDataGeneratorClass(): string;

    /**
     * Set appropriate options for the specific Dispatcher class we're testing
     *
     * @return array<string, string>
     */
    private function generateDispatcherOptions(): array
    {
        return [
            'dataGenerator' => $this->getDataGeneratorClass(),
            'dispatcher' => $this->getDispatcherClass(),
        ];
    }

    /**
     * @dataProvider provideFoundDispatchCases
     *
     * @param array<string, string> $argDict
     */
    public function testFoundDispatches(
        string $method,
        string $uri,
        callable $callback,
        string $handler,
        array $argDict
    ): void {
        $dispatcher = simpleDispatcher($callback, $this->generateDispatcherOptions());
        $info = $dispatcher->dispatch($method, $uri);

        self::assertSame($dispatcher::FOUND, $info[0]);
        self::assertSame($handler, $info[1]);
        self::assertSame($argDict, $info[2]);
    }

    /**
     * @dataProvider provideNotFoundDispatchCases
     */
    public function testNotFoundDispatches(string $method, string $uri, callable $callback): void
    {
        $dispatcher = simpleDispatcher($callback, $this->generateDispatcherOptions());
        $routeInfo = $dispatcher->dispatch($method, $uri);
        self::assertArrayNotHasKey(
            1,
            $routeInfo,
            'NOT_FOUND result must only contain a single element in the returned info array'
        );
        self::assertSame($dispatcher::NOT_FOUND, $routeInfo[0]);
    }

    /**
     * @dataProvider provideMethodNotAllowedDispatchCases
     *
     * @param string[] $availableMethods
     */
    public function testMethodNotAllowedDispatches(
        string $method,
        string $uri,
        callable $callback,
        array $availableMethods
    ): void {
        $dispatcher = simpleDispatcher($callback, $this->generateDispatcherOptions());
        $routeInfo = $dispatcher->dispatch($method, $uri);
        self::assertArrayHasKey(
            1,
            $routeInfo,
            'METHOD_NOT_ALLOWED result must return an array of allowed methods at index 1'
        );

        [$routedStatus, $methodArray] = $dispatcher->dispatch($method, $uri);
        self::assertSame($dispatcher::METHOD_NOT_ALLOWED, $routedStatus);
        self::assertSame($availableMethods, $methodArray);
    }

    public function testDuplicateVariableNameError(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Cannot use the same placeholder "test" twice');

        simpleDispatcher(static function (RouteCollector $r): void {
            $r->addRoute('GET', '/foo/{test}/{test:\d+}', 'handler0');
        }, $this->generateDispatcherOptions());
    }

    public function testDuplicateVariableRoute(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Cannot register two routes matching "/user/([^/]+)" for method "GET"');

        simpleDispatcher(static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{id}', 'handler0'); // oops, forgot \d+ restriction ;)
            $r->addRoute('GET', '/user/{name}', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    public function testDuplicateStaticRoute(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Cannot register two routes matching "/user" for method "GET"');

        simpleDispatcher(static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user', 'handler0');
            $r->addRoute('GET', '/user', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    public function testShadowedStaticRoute(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Static route "/user/nikic" is shadowed by previously defined variable route "/user/([^/]+)" for method "GET"');

        simpleDispatcher(static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/nikic', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    public function testCapturing(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Regex "(en|de)" for parameter "lang" contains a capturing group');

        simpleDispatcher(static function (RouteCollector $r): void {
            $r->addRoute('GET', '/{lang:(en|de)}', 'handler0');
        }, $this->generateDispatcherOptions());
    }

    /**
     * @return mixed[]
     */
    public function provideFoundDispatchCases(): array
    {
        $cases = [];

        // 0 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        $method = 'GET';
        $uri = '/resource/123/456';
        $handler = 'handler0';
        $argDict = [];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 1 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/handler0', 'handler0');
            $r->addRoute('GET', '/handler1', 'handler1');
            $r->addRoute('GET', '/handler2', 'handler2');
        };

        $method = 'GET';
        $uri = '/handler2';
        $handler = 'handler2';
        $argDict = [];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 2 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/user/{name}', 'handler2');
        };

        $method = 'GET';
        $uri = '/user/rdlowrey';
        $handler = 'handler2';
        $argDict = ['name' => 'rdlowrey'];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 3 -------------------------------------------------------------------------------------->

        // reuse $callback from #2

        $method = 'GET';
        $uri = '/user/12345';
        $handler = 'handler1';
        $argDict = ['id' => '12345'];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 4 -------------------------------------------------------------------------------------->

        // reuse $callback from #3

        $method = 'GET';
        $uri = '/user/NaN';
        $handler = 'handler2';
        $argDict = ['name' => 'NaN'];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 5 -------------------------------------------------------------------------------------->

        // reuse $callback from #4

        $method = 'GET';
        $uri = '/user/rdlowrey/12345';
        $handler = 'handler0';
        $argDict = ['name' => 'rdlowrey', 'id' => '12345'];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 6 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/12345/extension', 'handler1');
            $r->addRoute('GET', '/user/{id:[0-9]+}.{extension}', 'handler2');
        };

        $method = 'GET';
        $uri = '/user/12345.svg';
        $handler = 'handler2';
        $argDict = ['id' => '12345', 'extension' => 'svg'];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 7 ----- Test GET method fallback on HEAD route miss ------------------------------------>

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/static0', 'handler2');
            $r->addRoute('GET', '/static1', 'handler3');
            $r->addRoute('HEAD', '/static1', 'handler4');
        };

        $method = 'HEAD';
        $uri = '/user/rdlowrey';
        $handler = 'handler0';
        $argDict = ['name' => 'rdlowrey'];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 8 ----- Test GET method fallback on HEAD route miss ------------------------------------>

        // reuse $callback from #7

        $method = 'HEAD';
        $uri = '/user/rdlowrey/1234';
        $handler = 'handler1';
        $argDict = ['name' => 'rdlowrey', 'id' => '1234'];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 9 ----- Test GET method fallback on HEAD route miss ------------------------------------>

        // reuse $callback from #8

        $method = 'HEAD';
        $uri = '/static0';
        $handler = 'handler2';
        $argDict = [];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 10 ---- Test existing HEAD route used if available (no fallback) ----------------------->

        // reuse $callback from #9

        $method = 'HEAD';
        $uri = '/static1';
        $handler = 'handler4';
        $argDict = [];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 11 ---- More specified routes are not shadowed by less specific of another method ------>

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('POST', '/user/{name:[a-z]+}', 'handler1');
        };

        $method = 'POST';
        $uri = '/user/rdlowrey';
        $handler = 'handler1';
        $argDict = ['name' => 'rdlowrey'];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 12 ---- Handler of more specific routes is used, if it occurs first -------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('POST', '/user/{name:[a-z]+}', 'handler1');
            $r->addRoute('POST', '/user/{name}', 'handler2');
        };

        $method = 'POST';
        $uri = '/user/rdlowrey';
        $handler = 'handler1';
        $argDict = ['name' => 'rdlowrey'];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 13 ---- Route with constant suffix ----------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/{name}/edit', 'handler1');
        };

        $method = 'GET';
        $uri = '/user/rdlowrey/edit';
        $handler = 'handler1';
        $argDict = ['name' => 'rdlowrey'];

        $cases[] = [$method, $uri, $callback, $handler, $argDict];

        // 14 ---- Handle multiple methods with the same handler ---------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute(['GET', 'POST'], '/user', 'handlerGetPost');
            $r->addRoute(['DELETE'], '/user', 'handlerDelete');
            $r->addRoute([], '/user', 'handlerNone');
        };

        $argDict = [];
        $cases[] = ['GET', '/user', $callback, 'handlerGetPost', $argDict];
        $cases[] = ['POST', '/user', $callback, 'handlerGetPost', $argDict];
        $cases[] = ['DELETE', '/user', $callback, 'handlerDelete', $argDict];

        // 17 ----

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('POST', '/user.json', 'handler0');
            $r->addRoute('GET', '/{entity}.json', 'handler1');
        };

        $cases[] = ['GET', '/user.json', $callback, 'handler1', ['entity' => 'user']];

        // 18 ----

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '', 'handler0');
        };

        $cases[] = ['GET', '', $callback, 'handler0', []];

        // 19 ----

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('HEAD', '/a/{foo}', 'handler0');
            $r->addRoute('GET', '/b/{foo}', 'handler1');
        };

        $cases[] = ['HEAD', '/b/bar', $callback, 'handler1', ['foo' => 'bar']];

        // 20 ----

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('HEAD', '/a', 'handler0');
            $r->addRoute('GET', '/b', 'handler1');
        };

        $cases[] = ['HEAD', '/b', $callback, 'handler1', []];

        // 21 ----

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/foo', 'handler0');
            $r->addRoute('HEAD', '/{bar}', 'handler1');
        };

        $cases[] = ['HEAD', '/foo', $callback, 'handler1', ['bar' => 'foo']];

        // 22 ----

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('*', '/user', 'handler0');
            $r->addRoute('*', '/{user}', 'handler1');
            $r->addRoute('GET', '/user', 'handler2');
        };

        $cases[] = ['GET', '/user', $callback, 'handler2', []];

        // 23 ----

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('*', '/user', 'handler0');
            $r->addRoute('GET', '/user', 'handler1');
        };

        $cases[] = ['POST', '/user', $callback, 'handler0', []];

        // 24 ----

        $cases[] = ['HEAD', '/user', $callback, 'handler1', []];

        // 25 ----

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/{bar}', 'handler0');
            $r->addRoute('*', '/foo', 'handler1');
        };

        $cases[] = ['GET', '/foo', $callback, 'handler0', ['bar' => 'foo']];

        // 26 ----

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user', 'handler0');
            $r->addRoute('*', '/{foo:.*}', 'handler1');
        };

        $cases[] = ['POST', '/bar', $callback, 'handler1', ['foo' => 'bar']];

        // 27 ----

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('OPTIONS', '/about', 'handler0');
        };

        $cases[] = ['OPTIONS', '/about', $callback, 'handler0', []];

        // x -------------------------------------------------------------------------------------->

        return $cases;
    }

    /**
     * @return mixed[]
     */
    public function provideNotFoundDispatchCases(): array
    {
        $cases = [];

        // 0 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        $method = 'GET';
        $uri = '/not-found';

        $cases[] = [$method, $uri, $callback];

        // 1 -------------------------------------------------------------------------------------->

        // reuse callback from #0
        $method = 'POST';
        $uri = '/not-found';

        $cases[] = [$method, $uri, $callback];

        // 2 -------------------------------------------------------------------------------------->

        // reuse callback from #1
        $method = 'PUT';
        $uri = '/not-found';

        $cases[] = [$method, $uri, $callback];

        // 3 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/handler0', 'handler0');
            $r->addRoute('GET', '/handler1', 'handler1');
            $r->addRoute('GET', '/handler2', 'handler2');
        };

        $method = 'GET';
        $uri = '/not-found';

        $cases[] = [$method, $uri, $callback];

        // 4 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/user/{name}', 'handler2');
        };

        $method = 'GET';
        $uri = '/not-found';

        $cases[] = [$method, $uri, $callback];

        // 5 -------------------------------------------------------------------------------------->

        // reuse callback from #4
        $method = 'GET';
        $uri = '/user/rdlowrey/12345/not-found';

        $cases[] = [$method, $uri, $callback];

        // 6 -------------------------------------------------------------------------------------->

        // reuse callback from #5
        $method = 'HEAD';

        $cases[] = [$method, $uri, $callback];

        // x -------------------------------------------------------------------------------------->

        return $cases;
    }

    /**
     * @return mixed[]
     */
    public function provideMethodNotAllowedDispatchCases(): array
    {
        $cases = [];

        // 0 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        $method = 'POST';
        $uri = '/resource/123/456';
        $allowedMethods = ['GET'];

        $cases[] = [$method, $uri, $callback, $allowedMethods];

        // 1 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
            $r->addRoute('POST', '/resource/123/456', 'handler1');
            $r->addRoute('PUT', '/resource/123/456', 'handler2');
            $r->addRoute('*', '/', 'handler3');
        };

        $method = 'DELETE';
        $uri = '/resource/123/456';
        $allowedMethods = ['GET', 'POST', 'PUT'];

        $cases[] = [$method, $uri, $callback, $allowedMethods];

        // 2 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('POST', '/user/{name}/{id:[0-9]+}', 'handler1');
            $r->addRoute('PUT', '/user/{name}/{id:[0-9]+}', 'handler2');
            $r->addRoute('PATCH', '/user/{name}/{id:[0-9]+}', 'handler3');
        };

        $method = 'DELETE';
        $uri = '/user/rdlowrey/42';
        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH'];

        $cases[] = [$method, $uri, $callback, $allowedMethods];

        // 3 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('POST', '/user/{name}', 'handler1');
            $r->addRoute('PUT', '/user/{name:[a-z]+}', 'handler2');
            $r->addRoute('PATCH', '/user/{name:[a-z]+}', 'handler3');
        };

        $method = 'GET';
        $uri = '/user/rdlowrey';
        $allowedMethods = ['POST', 'PUT', 'PATCH'];

        $cases[] = [$method, $uri, $callback, $allowedMethods];

        // 4 -------------------------------------------------------------------------------------->

        $callback = static function (RouteCollector $r): void {
            $r->addRoute(['GET', 'POST'], '/user', 'handlerGetPost');
            $r->addRoute(['DELETE'], '/user', 'handlerDelete');
            $r->addRoute([], '/user', 'handlerNone');
        };

        $cases[] = ['PUT', '/user', $callback, ['GET', 'POST', 'DELETE']];

        // 5

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('POST', '/user.json', 'handler0');
            $r->addRoute('GET', '/{entity}.json', 'handler1');
        };

        $cases[] = ['PUT', '/user.json', $callback, ['POST', 'GET']];

        // x -------------------------------------------------------------------------------------->

        return $cases;
    }
}
