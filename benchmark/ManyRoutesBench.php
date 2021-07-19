<?php
declare(strict_types=1);

namespace FastRoute\Benchmark;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;

final class ManyRoutesBench extends Benchmark
{
    private const ROUTES_COUNT = 400;

    /** @inheritdoc */
    protected function createDispatcher(array $options): Dispatcher
    {
        return simpleDispatcher(
            static function (RouteCollector $routes): void {
                for ($i = 0; $i < self::ROUTES_COUNT; ++$i) {
                    $routes->addRoute('GET', '/abc' . $i, ['name' => 'static-' . $i]);
                    $routes->addRoute('GET', '/abc{foo}/' . $i, ['name' => 'not-static-' . $i]);
                }
            },
            $options
        );
    }

    /** @inheritdoc */
    public function provideStaticRoutes(): iterable
    {
        yield 'first' => [
            'method' => 'GET',
            'route' => '/abc0',
            'result' => [Dispatcher::FOUND, ['name' => 'static-0'], []],
        ];

        yield 'last' => [
            'method' => 'GET',
            'route' => '/abc' . (self::ROUTES_COUNT - 1),
            'result' => [Dispatcher::FOUND, ['name' => 'static-' . (self::ROUTES_COUNT - 1)], []],
        ];

        yield 'invalid-method' => [
            'method' => 'PUT',
            'route' => '/abc' . (self::ROUTES_COUNT - 1),
            'result' => [Dispatcher::METHOD_NOT_ALLOWED, ['GET']],
        ];
    }

    /** @inheritdoc */
    public function provideDynamicRoutes(): iterable
    {
        yield 'first' => [
            'method' => 'GET',
            'route' => '/abcbar/0',
            'result' => [Dispatcher::FOUND, ['name' => 'not-static-0'], ['foo' => 'bar']],
        ];

        yield 'last' => [
            'method' => 'GET',
            'route' => '/abcbar/' . (self::ROUTES_COUNT - 1),
            'result' => [Dispatcher::FOUND, ['name' => 'not-static-' . (self::ROUTES_COUNT - 1)], ['foo' => 'bar']],
        ];

        yield 'invalid-method' => [
            'method' => 'PUT',
            'route' => '/abcbar/' . (self::ROUTES_COUNT - 1),
            'result' => [Dispatcher::METHOD_NOT_ALLOWED, ['GET']],
        ];
    }

    /** @inheritdoc */
    public function provideOtherScenarios(): iterable
    {
        yield 'non-existent' => [
            'method' => 'GET',
            'route' => '/testing',
            'result' => [Dispatcher::NOT_FOUND],
        ];
    }
}
