<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use Closure;
use FastRoute\BadRouteException;
use FastRoute\ConfigureRoutes;
use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\Result\Matched;
use FastRoute\Dispatcher\Result\MethodNotAllowed;
use FastRoute\Dispatcher\Result\NotMatched;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

use function FastRoute\simpleDispatcher;

/** @phpstan-import-type ExtraParameters from DataGenerator */
abstract class DispatcherTestCase extends TestCase
{
    /**
     * Delegate dispatcher selection to child test classes
     *
     * @return class-string<Dispatcher>
     */
    abstract protected function getDispatcherClass(): string;

    /**
     * Delegate dataGenerator selection to child test classes
     *
     * @return class-string<DataGenerator>
     */
    abstract protected function getDataGeneratorClass(): string;

    /**
     * Set appropriate options for the specific Dispatcher class we're testing
     *
     * @return array{dataGenerator: class-string<DataGenerator>, dispatcher: class-string<Dispatcher>}
     */
    private function generateDispatcherOptions(): array
    {
        return [
            'dataGenerator' => $this->getDataGeneratorClass(),
            'dispatcher' => $this->getDispatcherClass(),
        ];
    }

    /**
     * @param array<string, string> $argDict
     * @param ExtraParameters       $extraParameters
     */
    #[PHPUnit\Test]
    #[PHPUnit\DataProvider('provideFoundDispatchCases')]
    public function foundDispatches(
        string $method,
        string $uri,
        callable $callback,
        string $handler,
        array $argDict = [],
        array $extraParameters = [],
    ): void {
        $dispatcher = simpleDispatcher($callback, $this->generateDispatcherOptions());
        $info = $dispatcher->dispatch($method, $uri);

        self::assertInstanceOf(Matched::class, $info);
        self::assertSame($handler, $info->handler);
        self::assertSame($argDict, $info->variables);
        self::assertSame($extraParameters, $info->extraParameters);

        // BC-compatibility checks
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

        self::assertInstanceOf(NotMatched::class, $routeInfo);

        // BC-compatibility checks
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

        self::assertInstanceOf(MethodNotAllowed::class, $routeInfo);
        self::assertSame($availableMethods, $routeInfo->allowedMethods);

        // BC-compatibility checks
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

        simpleDispatcher(static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/foo/{test}/{test:\d+}', 'handler0');
        }, $this->generateDispatcherOptions());
    }

    #[PHPUnit\Test]
    public function duplicateVariableRoute(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Cannot register two routes matching "/user/([^/]+)" for method "GET"');

        simpleDispatcher(static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user/{id}', 'handler0'); // oops, forgot \d+ restriction ;)
            $r->addRoute('GET', '/user/{name}', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    #[PHPUnit\Test]
    public function duplicateStaticRoute(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Cannot register two routes matching "/user" for method "GET"');

        simpleDispatcher(static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user', 'handler0');
            $r->addRoute('GET', '/user', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    #[PHPUnit\Test]
    public function shadowedStaticRoute(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Static route "/user/nikic" is shadowed by previously defined variable route "/user/([^/]+)" for method "GET"');

        simpleDispatcher(static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/nikic', 'handler1');
        }, $this->generateDispatcherOptions());
    }

    #[PHPUnit\Test]
    public function capturing(): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage('Regex "(en|de)" for parameter "lang" contains a capturing group');

        simpleDispatcher(static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/{lang:(en|de)}', 'handler0');
        }, $this->generateDispatcherOptions());
    }

    /** @return iterable<string, array{0: string, 1: string, 2: Closure(ConfigureRoutes):void, 3: string, 4?: array<string, string>}> */
    public static function provideFoundDispatchCases(): iterable
    {
        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        yield 'single static route' => ['GET', '/resource/123/456', $callback, 'handler0', [], ['_route' => '/resource/123/456']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/handler0', 'handler0');
            $r->addRoute('GET', '/handler1', 'handler1');
            $r->addRoute('GET', '/handler2', 'handler2');
        };

        yield 'multiple static routes' => ['GET', '/handler2', $callback, 'handler2', [], ['_route' => '/handler2']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/user/{name}', 'handler2');
        };

        yield 'parameter matching precedence {/user/rdlowrey/12345}' => ['GET', '/user/rdlowrey/12345', $callback, 'handler0', ['name' => 'rdlowrey', 'id' => '12345'], ['_route' => '/user/{name}/{id:[0-9]+}']];
        yield 'parameter matching precedence {/user/12345}' => ['GET', '/user/12345', $callback, 'handler1', ['id' => '12345'], ['_route' => '/user/{id:[0-9]+}']];
        yield 'parameter matching precedence {/user/rdlowrey}' => ['GET', '/user/rdlowrey', $callback, 'handler2', ['name' => 'rdlowrey'], ['_route' => '/user/{name}']];
        yield 'parameter matching precedence {/user/NaN}' => ['GET', '/user/NaN', $callback, 'handler2', ['name' => 'NaN'], ['_route' => '/user/{name}']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler0');
            $r->addRoute('GET', '/user/12345/extension', 'handler1');
            $r->addRoute('GET', '/user/{id:[0-9]+}.{extension}', 'handler2');
        };

        yield 'dynamic file extensions' => ['GET', '/user/12345.svg', $callback, 'handler2', ['id' => '12345', 'extension' => 'svg'], ['_route' => '/user/{id:[0-9]+}.{extension}']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler1');
            $r->addRoute('GET', '/static0', 'handler2');
            $r->addRoute('GET', '/static1', 'handler3');
            $r->addRoute('HEAD', '/static1', 'handler4');
        };

        yield 'fallback to GET on HEAD route miss {/user/rdlowrey}' => ['HEAD', '/user/rdlowrey', $callback, 'handler0', ['name' => 'rdlowrey'], ['_route' => '/user/{name}']];
        yield 'fallback to GET on HEAD route miss {/user/rdlowrey/1234}' => ['HEAD', '/user/rdlowrey/1234', $callback, 'handler1', ['name' => 'rdlowrey', 'id' => '1234'], ['_route' => '/user/{name}/{id:[0-9]+}']];
        yield 'fallback to GET on HEAD route miss {/static0}' => ['HEAD', '/static0', $callback, 'handler2', [], ['_route' => '/static0']];
        yield 'registered HEAD route is used' => ['HEAD', '/static1', $callback, 'handler4', [], ['_route' => '/static1']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('POST', '/user/{name:[a-z]+}', 'handler1');
        };

        yield 'more specific routes are not shadowed by less specific of another method' => ['POST', '/user/rdlowrey', $callback, 'handler1', ['name' => 'rdlowrey'], ['_route' => '/user/{name:[a-z]+}']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('POST', '/user/{name:[a-z]+}', 'handler1');
            $r->addRoute('POST', '/user/{name}', 'handler2');
        };

        yield 'more specific routes are used, according to the registration order {/user/rdlowrey}' => ['POST', '/user/rdlowrey', $callback, 'handler1', ['name' => 'rdlowrey'], ['_route' => '/user/{name:[a-z]+}']];
        yield 'more specific routes are used, according to the registration order {/user/rdlowrey1}' => ['POST', '/user/rdlowrey1', $callback, 'handler2', ['name' => 'rdlowrey1'], ['_route' => '/user/{name}']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user/{name}', 'handler0');
            $r->addRoute('GET', '/user/{name}/edit', 'handler1');
        };

        yield 'route with constant suffix' => ['GET', '/user/rdlowrey/edit', $callback, 'handler1', ['name' => 'rdlowrey'], ['_route' => '/user/{name}/edit']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute(['GET', 'POST'], '/user', 'handlerGetPost');
            $r->addRoute(['DELETE'], '/user', 'handlerDelete');
            $r->addRoute([], '/user', 'handlerNone');
        };

        foreach (['GET' => 'handlerGetPost', 'POST' => 'handlerGetPost', 'DELETE' => 'handlerDelete'] as $method => $handler) {
            yield 'multiple methods with the same handler {' . $method . '}' => [$method, '/user', $callback, $handler, [], ['_route' => '/user']];
        }

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('POST', '/user.json', 'handler0');
            $r->addRoute('GET', '/{entity}.json', 'handler1');
        };

        yield 'fallback to dynamic routes when method does not match' => ['GET', '/user.json', $callback, 'handler1', ['entity' => 'user'], ['_route' => '/{entity}.json']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '', 'handler0');
        };

        yield 'match empty route' => ['GET', '', $callback, 'handler0', [], ['_route' => '']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('HEAD', '/a/{foo}', 'handler0');
            $r->addRoute('GET', '/b/{foo}', 'handler1');
        };

        yield 'fallback to GET route on HEAD miss {dynamic routes}' => ['HEAD', '/b/bar', $callback, 'handler1', ['foo' => 'bar'], ['_route' => '/b/{foo}']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('HEAD', '/a', 'handler0');
            $r->addRoute('GET', '/b', 'handler1');
        };

        yield 'fallback to GET route on HEAD miss {static routes}' =>  ['HEAD', '/b', $callback, 'handler1', [], ['_route' => '/b']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/foo', 'handler0');
            $r->addRoute('HEAD', '/{bar}', 'handler1');
        };

        yield 'fallback to GET route on HEAD miss {dynamic/static routes}' => ['HEAD', '/foo', $callback, 'handler1', ['bar' => 'foo'], ['_route' => '/{bar}']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('*', '/user', 'handler0');
            $r->addRoute('*', '/{user}', 'handler1');
            $r->addRoute('GET', '/user', 'handler2');
        };

        yield 'fallback method is used when needed {GET,static}' => ['GET', '/user', $callback, 'handler2', [], ['_route' => '/user']];
        yield 'fallback method is used when needed {HEAD,static}' => ['HEAD', '/user', $callback, 'handler2', [], ['_route' => '/user']];

        yield 'fallback method is used when needed {GET,dynamic}' => ['GET', '/foo', $callback, 'handler1', ['user' => 'foo'], ['_route' => '/{user}']];
        yield 'fallback method is used when needed {HEAD,dynamic}' => ['HEAD', '/foo', $callback, 'handler1', ['user' => 'foo'], ['_route' => '/{user}']];

        foreach (['POST', 'DELETE', 'OPTIONS', 'PUT', 'PATCH'] as $method) {
            yield 'fallback method is used when needed {' . $method . ',static}' => [$method, '/user', $callback, 'handler0', [], ['_route' => '/user']];
            yield 'fallback method is used when needed {' . $method . ',dynamic}' => [$method, '/foo', $callback, 'handler1', ['user' => 'foo'], ['_route' => '/{user}']];
        }

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/{bar}', 'handler0');
            $r->addRoute('*', '/foo', 'handler1');
        };

        yield 'fallback method is used as last resource' => ['GET', '/foo', $callback, 'handler0', ['bar' => 'foo'], ['_route' => '/{bar}']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user', 'handler0');
            $r->addRoute('*', '/{foo:.*}', 'handler1');
        };

        yield 'fallback method can capture arguments' => ['POST', '/bar', $callback, 'handler1', ['foo' => 'bar'], ['_route' => '/{foo:.*}']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('OPTIONS', '/about', 'handler0');
        };

        yield 'options method is supported' => ['OPTIONS', '/about', $callback, 'handler0', [], ['_route' => '/about']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/about[/{aboutwhat}[/location]]', 'handler0');
        };

        yield 'Paths can be placed after an optional placeholder' => ['GET', '/about/some/location', $callback, 'handler0', ['aboutwhat' => 'some'], ['_route' => '/about[/{aboutwhat}[/location]]']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/about[/{aboutwhat:.*}[/location]]', 'handler0');
        };

        yield 'Paths can be placed after an unlimited optional placeholder' => ['GET', '/about/the/nested/location', $callback, 'handler0', ['aboutwhat' => 'the/nested'], ['_route' => '/about[/{aboutwhat:.*}[/location]]']];
    }

    /** @return iterable<string, array{string, string, Closure(ConfigureRoutes):void}> */
    public static function provideNotFoundDispatchCases(): iterable
    {
        $methods = ['GET', 'POST', 'DELETE', 'PUT', 'HEAD', 'OPTIONS'];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        foreach ($methods as $method) {
            yield 'single static route {' . $method . '}' => [$method, '/not-found', $callback];
        }

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/handler0', 'handler0');
            $r->addRoute('GET', '/handler1', 'handler1');
            $r->addRoute('GET', '/handler2', 'handler2');
        };

        foreach ($methods as $method) {
            yield 'multiple static routes {' . $method . '}' => [$method, '/not-found', $callback];
        }

        $callback = static function (ConfigureRoutes $r): void {
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

    /** @return iterable<string, array{string, string, Closure(ConfigureRoutes):void, list<string>}> */
    public static function provideMethodNotAllowedDispatchCases(): iterable
    {
        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
        };

        yield 'match static routes' => ['POST', '/resource/123/456', $callback, ['GET']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/resource/123/456', 'handler0');
            $r->addRoute('POST', '/resource/123/456', 'handler1');
            $r->addRoute('PUT', '/resource/123/456', 'handler2');
            $r->addRoute('*', '/', 'handler3');
        };

        yield 'ignore fallbacks for unmatched routes ' => ['DELETE', '/resource/123/456', $callback, ['GET', 'POST', 'PUT']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
            $r->addRoute('POST', '/user/{name}/{id:[0-9]+}', 'handler1');
            $r->addRoute('PUT', '/user/{name}/{id:[0-9]+}', 'handler2');
            $r->addRoute('PATCH', '/user/{name}/{id:[0-9]+}', 'handler3');
        };

        yield 'match dynamic routes' => ['DELETE', '/user/rdlowrey/42', $callback, ['GET', 'POST', 'PUT', 'PATCH']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('POST', '/user/{name}', 'handler1');
            $r->addRoute('PUT', '/user/{name:[a-z]+}', 'handler2');
            $r->addRoute('PATCH', '/user/{name:[a-z]+}', 'handler3');
        };

        yield 'match with and without validations' => ['GET', '/user/rdlowrey', $callback, ['POST', 'PUT', 'PATCH']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('POST', '/user/{name}', 'handler1');
            $r->addRoute('PUT', '/user/{name:[a-z]+}', 'handler2');
            $r->addRoute('PATCH', '/user/{name:[a-z]+}', 'handler3');
            $r->addRoute('DELETE', '/user/{name:[a-z0-9]+}', 'handler3');
        };

        yield 'match respects validations' => ['GET', '/user/rdlowrey42', $callback, ['POST', 'DELETE']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute(['GET', 'POST'], '/user', 'handlerGetPost');
            $r->addRoute(['DELETE'], '/user', 'handlerDelete');
            $r->addRoute([], '/user', 'handlerNone');
        };

        yield 'match all valid methods' => ['PUT', '/user', $callback, ['GET', 'POST', 'DELETE']];

        $callback = static function (ConfigureRoutes $r): void {
            $r->addRoute('POST', '/user.json', 'handler0');
            $r->addRoute('GET', '/{entity}.json', 'handler1');
        };

        yield 'match static and dynamic routes' => ['PUT', '/user.json', $callback, ['POST', 'GET']];
    }
}
