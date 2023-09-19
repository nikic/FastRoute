<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use Closure;
use FastRoute\BadRouteException;
use FastRoute\RouteCollector;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

use function FastRoute\simpleDispatcher;

abstract class DispatcherTestCase extends TestCase
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
     * @return array{dataGenerator: string, dispatcher: string}
     */
    private function generateDispatcherOptions(): array
    {
        return [
            'dataGenerator' => $this->getDataGeneratorClass(),
            'dispatcher' => $this->getDispatcherClass(),
        ];
    }

    /** @param array<string, string> $argDict */
    #[PHPUnit\Test]
    #[PHPUnit\DataProvider('provideFoundDispatchCases')]
    public function foundDispatches(
        string $method,
        string $uri,
        callable $callback,
        string $handler,
        array $argDict = [],
    ): void {
        $dispatcher = simpleDispatcher($callback, $this->generateDispatcherOptions());
        $info = $dispatcher->dispatch($method, $uri);

        self::assertSame($dispatcher::FOUND, $info[0]);
        self::assertSame($handler, $info[1]);
        self::assertSame($argDict, $info[2]);
    }

    #[PHPUnit\Test]
    #[PHPUnit\DataProvider('provideNotFoundDispatchCases')]
    public function notFoundDispatches(string $method, string $uri, callable $callback): void
    {
        $dispatcher = simpleDispatcher($callback, $this->generateDispatcherOptions());
        $routeInfo = $dispatcher->dispatch($method, $uri);
        self::assertArrayNotHasKey(
            1,
            $routeInfo,
            'NOT_FOUND result must only contain a single element in the returned info array',
        );
        self::assertSame($dispatcher::NOT_FOUND, $routeInfo[0]);
    }

    /** @param string[] $availableMethods */
    #[PHPUnit\Test]
    #[PHPUnit\DataProvider('provideMethodNotAllowedDispatchCases')]
    public function methodNotAllowedDispatches(
        string $method,
        string $uri,
        callable $callback,
        array $availableMethods,
    ): void {
        $dispatcher = simpleDispatcher($callback, $this->generateDispatcherOptions());
        $routeInfo = $dispatcher->dispatch($method, $uri);
        self::assertArrayHasKey(
            1,
            $routeInfo,
            'METHOD_NOT_ALLOWED result must return an array of allowed methods at index 1',
        );

        [$routedStatus, $methodArray] = $dispatcher->dispatch($method, $uri);
        self::assertSame($dispatcher::METHOD_NOT_ALLOWED, $routedStatus);
        self::assertSame($availableMethods, $methodArray);
    }

    #[PHPUnit\Test]
    public function duplicateVariableNameError(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Cannot use the same placeholder "test" twice');

        simpleDispatcher(static function (RouteCollector $r): void {
            $r->addRoute('GET', '/foo/{test}/{test:\d+}', 'handler0');
        }, $this->generateDispatcherOptions());
    }

    #[PHPUnit\Test]
    public function duplicateVariableRoute(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Cannot register two routes matching "/user/([^/]+)" for method "GET"');

        simpleDispatcher(static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{id}', 'handler0'); // oops, forgot \d+ restriction ;)
            $r->addRoute('GET', '/user/{name}', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    #[PHPUnit\Test]
    public function duplicateStaticRoute(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Cannot register two routes matching "/user" for method "GET"');

        simpleDispatcher(static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user', 'handler0');
            $r->addRoute('GET', '/user', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    #[PHPUnit\Test]
    public function shadowedStaticRoute(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Static route "/user/nikic" is shadowed by previously defined variable route "/user/([^/]+)" for method "GET"');

        simpleDispatcher(static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/nikic', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    #[PHPUnit\Test]
    public function capturing(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Regex "(en|de)" for parameter "lang" contains a capturing group');

        simpleDispatcher(static function (RouteCollector $r): void {
            $r->addRoute('GET', '/{lang:(en|de)}', 'handler0');
        }, $this->generateDispatcherOptions());
    }

    /** @return iterable<string, array{0: string, 1: string, 2: Closure(RouteCollector):void, 3: string, 4?: array<string, string>}> */
    public static function provideFoundDispatchCases(): iterable
    {
        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        yield 'single static route' => ['GET', '/resource/123/456', $callback, 'handler0'];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/handler0', 'handler0');
            $r->addRoute('GET', '/handler1', 'handler1');
            $r->addRoute('GET', '/handler2', 'handler2');
        };

        yield 'multiple static routes' => ['GET', '/handler2', $callback, 'handler2'];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/user/{name}', 'handler2');
        };

        yield 'parameter matching precedence {/user/rdlowrey/12345}' => ['GET', '/user/rdlowrey/12345', $callback, 'handler0', ['name' => 'rdlowrey', 'id' => '12345']];
        yield 'parameter matching precedence {/user/12345}' => ['GET', '/user/12345', $callback, 'handler1', ['id' => '12345']];
        yield 'parameter matching precedence {/user/rdlowrey}' => ['GET', '/user/rdlowrey', $callback, 'handler2', ['name' => 'rdlowrey']];
        yield 'parameter matching precedence {/user/NaN}' => ['GET', '/user/NaN', $callback, 'handler2', ['name' => 'NaN']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/12345/extension', 'handler1');
            $r->addRoute('GET', '/user/{id:[0-9]+}.{extension}', 'handler2');
        };

        yield 'dynamic file extensions' => ['GET', '/user/12345.svg', $callback, 'handler2', ['id' => '12345', 'extension' => 'svg']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/static0', 'handler2');
            $r->addRoute('GET', '/static1', 'handler3');
            $r->addRoute('HEAD', '/static1', 'handler4');
        };

        yield 'fallback to GET on HEAD route miss {/user/rdlowrey}' => ['HEAD', '/user/rdlowrey', $callback, 'handler0', ['name' => 'rdlowrey']];
        yield 'fallback to GET on HEAD route miss {/user/rdlowrey/1234}' => ['HEAD', '/user/rdlowrey/1234', $callback, 'handler1', ['name' => 'rdlowrey', 'id' => '1234']];
        yield 'fallback to GET on HEAD route miss {/static0}' => ['HEAD', '/static0', $callback, 'handler2'];
        yield 'registered HEAD route is used' => ['HEAD', '/static1', $callback, 'handler4'];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('POST', '/user/{name:[a-z]+}', 'handler1');
        };

        yield 'more specific routes are not shadowed by less specific of another method' => ['POST', '/user/rdlowrey', $callback, 'handler1', ['name' => 'rdlowrey']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('POST', '/user/{name:[a-z]+}', 'handler1');
            $r->addRoute('POST', '/user/{name}', 'handler2');
        };

        yield 'more specific routes are used, according to the registration order {/user/rdlowrey}' => ['POST', '/user/rdlowrey', $callback, 'handler1', ['name' => 'rdlowrey']];
        yield 'more specific routes are used, according to the registration order {/user/rdlowrey1}' => ['POST', '/user/rdlowrey1', $callback, 'handler2', ['name' => 'rdlowrey1']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/{name}/edit', 'handler1');
        };

        yield 'route with constant suffix' => ['GET', '/user/rdlowrey/edit', $callback, 'handler1', ['name' => 'rdlowrey']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute(['GET', 'POST'], '/user', 'handlerGetPost');
            $r->addRoute(['DELETE'], '/user', 'handlerDelete');
            $r->addRoute([], '/user', 'handlerNone');
        };

        foreach (['GET' => 'handlerGetPost', 'POST' => 'handlerGetPost', 'DELETE' => 'handlerDelete'] as $method => $handler) {
            yield 'multiple methods with the same handler {' . $method . '}' => [$method, '/user', $callback, $handler];
        }

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('POST', '/user.json', 'handler0');
            $r->addRoute('GET', '/{entity}.json', 'handler1');
        };

        yield 'fallback to dynamic routes when method does not match' => ['GET', '/user.json', $callback, 'handler1', ['entity' => 'user']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '', 'handler0');
        };

        yield 'match empty route' => ['GET', '', $callback, 'handler0'];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('HEAD', '/a/{foo}', 'handler0');
            $r->addRoute('GET', '/b/{foo}', 'handler1');
        };

        yield 'fallback to GET route on HEAD miss {dynamic routes}' => ['HEAD', '/b/bar', $callback, 'handler1', ['foo' => 'bar']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('HEAD', '/a', 'handler0');
            $r->addRoute('GET', '/b', 'handler1');
        };

        yield 'fallback to GET route on HEAD miss {static routes}' =>  ['HEAD', '/b', $callback, 'handler1'];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/foo', 'handler0');
            $r->addRoute('HEAD', '/{bar}', 'handler1');
        };

        yield 'fallback to GET route on HEAD miss {dynamic/static routes}' => ['HEAD', '/foo', $callback, 'handler1', ['bar' => 'foo']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('*', '/user', 'handler0');
            $r->addRoute('*', '/{user}', 'handler1');
            $r->addRoute('GET', '/user', 'handler2');
        };

        yield 'fallback method is used when needed {GET,static}' => ['GET', '/user', $callback, 'handler2'];
        yield 'fallback method is used when needed {HEAD,static}' => ['HEAD', '/user', $callback, 'handler2'];

        yield 'fallback method is used when needed {GET,dynamic}' => ['GET', '/foo', $callback, 'handler1', ['user' => 'foo']];
        yield 'fallback method is used when needed {HEAD,dynamic}' => ['HEAD', '/foo', $callback, 'handler1', ['user' => 'foo']];

        foreach (['POST', 'DELETE', 'OPTIONS', 'PUT', 'PATCH'] as $method) {
            yield 'fallback method is used when needed {' . $method . ',static}' => [$method, '/user', $callback, 'handler0'];
            yield 'fallback method is used when needed {' . $method . ',dynamic}' => [$method, '/foo', $callback, 'handler1', ['user' => 'foo']];
        }

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/{bar}', 'handler0');
            $r->addRoute('*', '/foo', 'handler1');
        };

        yield 'fallback method is used as last resource' => ['GET', '/foo', $callback, 'handler0', ['bar' => 'foo']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user', 'handler0');
            $r->addRoute('*', '/{foo:.*}', 'handler1');
        };

        yield 'fallback method can capture arguments' => ['POST', '/bar', $callback, 'handler1', ['foo' => 'bar']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('OPTIONS', '/about', 'handler0');
        };

        yield 'options method is supported' => ['OPTIONS', '/about', $callback, 'handler0'];
    }

    /** @return iterable<string, array{string, string, Closure(RouteCollector):void}> */
    public static function provideNotFoundDispatchCases(): iterable
    {
        $methods = ['GET', 'POST', 'DELETE', 'PUT', 'HEAD', 'OPTIONS'];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        foreach ($methods as $method) {
            yield 'single static route {' . $method . '}' => [$method, '/not-found', $callback];
        }

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/handler0', 'handler0');
            $r->addRoute('GET', '/handler1', 'handler1');
            $r->addRoute('GET', '/handler2', 'handler2');
        };

        foreach ($methods as $method) {
            yield 'multiple static routes {' . $method . '}' => [$method, '/not-found', $callback];
        }

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/user/{name}', 'handler2');
        };

        foreach ($methods as $method) {
            foreach (['/not-found', '/user/rdlowrey/12345/not-found'] as $uri) {
                yield 'multiple dynamic routes {' . $method . ', ' . $uri . '}' => [$method, $uri, $callback];
            }
        }
    }

    /** @return iterable<string, array{string, string, Closure(RouteCollector):void, list<string>}> */
    public static function provideMethodNotAllowedDispatchCases(): iterable
    {
        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        yield 'match static routes' => ['POST', '/resource/123/456', $callback, ['GET']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
            $r->addRoute('POST', '/resource/123/456', 'handler1');
            $r->addRoute('PUT', '/resource/123/456', 'handler2');
            $r->addRoute('*', '/', 'handler3');
        };

        yield 'ignore fallbacks for unmatched routes ' => ['DELETE', '/resource/123/456', $callback, ['GET', 'POST', 'PUT']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('POST', '/user/{name}/{id:[0-9]+}', 'handler1');
            $r->addRoute('PUT', '/user/{name}/{id:[0-9]+}', 'handler2');
            $r->addRoute('PATCH', '/user/{name}/{id:[0-9]+}', 'handler3');
        };

        yield 'match dynamic routes' => ['DELETE', '/user/rdlowrey/42', $callback, ['GET', 'POST', 'PUT', 'PATCH']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('POST', '/user/{name}', 'handler1');
            $r->addRoute('PUT', '/user/{name:[a-z]+}', 'handler2');
            $r->addRoute('PATCH', '/user/{name:[a-z]+}', 'handler3');
        };

        yield 'match with and without validations' => ['GET', '/user/rdlowrey', $callback, ['POST', 'PUT', 'PATCH']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('POST', '/user/{name}', 'handler1');
            $r->addRoute('PUT', '/user/{name:[a-z]+}', 'handler2');
            $r->addRoute('PATCH', '/user/{name:[a-z]+}', 'handler3');
            $r->addRoute('DELETE', '/user/{name:[a-z0-9]+}', 'handler3');
        };

        yield 'match respects validations' => ['GET', '/user/rdlowrey42', $callback, ['POST', 'DELETE']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute(['GET', 'POST'], '/user', 'handlerGetPost');
            $r->addRoute(['DELETE'], '/user', 'handlerDelete');
            $r->addRoute([], '/user', 'handlerNone');
        };

        yield 'match all valid methods' => ['PUT', '/user', $callback, ['GET', 'POST', 'DELETE']];

        $callback = static function (RouteCollector $r): void {
            $r->addRoute('POST', '/user.json', 'handler0');
            $r->addRoute('GET', '/{entity}.json', 'handler1');
        };

        yield 'match static and dynamic routes' => ['PUT', '/user.json', $callback, ['POST', 'GET']];
    }
}
