<?php
declare(strict_types=1);

namespace FastRoute\Test;

use FastRoute\BadRouteException;
use FastRoute\ConfigureRoutes;
use FastRoute\DataGenerator;
use FastRoute\RouteCollectorImmutable;
use FastRoute\RouteParser\Std;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

use function count;
use function is_string;

final class RouteCollectorImmutableTest extends TestCase
{
    #[PHPUnit\Test]
    public function shortcutsCanBeUsedToRegisterRoutes(): void
    {
        $r = self::routeCollector();

        $immutable = $r
            ->any('/any', 'any')
            ->delete('/delete', 'delete')
            ->get('/get', 'get')
            ->head('/head', 'head')
            ->patch('/patch', 'patch')
            ->post('/post', 'post')
            ->put('/put', 'put')
            ->options('/options', 'options');

        $expected = [
            ['*', '/any', 'any', ['_route' => '/any']],
            ['DELETE', '/delete', 'delete', ['_route' => '/delete']],
            ['GET', '/get', 'get', ['_route' => '/get']],
            ['HEAD', '/head', 'head', ['_route' => '/head']],
            ['PATCH', '/patch', 'patch', ['_route' => '/patch']],
            ['POST', '/post', 'post', ['_route' => '/post']],
            ['PUT', '/put', 'put', ['_route' => '/put']],
            ['OPTIONS', '/options', 'options', ['_route' => '/options']],
        ];

        self::assertSame($expected, $immutable->processedRoutes()[0]);
    }

    #[PHPUnit\Test]
    public function routesCanBeGrouped(): void
    {
        $r = self::routeCollector();

        $immutable = $r
            ->delete('/delete', 'delete')
            ->get('/get', 'get')
            ->head('/head', 'head')
            ->patch('/patch', 'patch')
            ->post('/post', 'post')
            ->put('/put', 'put')
            ->options('/options', 'options');

        $immutable = $immutable->addGroup('/group-one', static function (ConfigureRoutes $r1): ConfigureRoutes {
            $immutable1 = $r1
                ->delete('/delete', 'delete')
                ->get('/get', 'get')
                ->head('/head', 'head')
                ->patch('/patch', 'patch')
                ->post('/post', 'post')
                ->put('/put', 'put')
                ->options('/options', 'options');

            return $immutable1->addGroup('/group-two', static function (ConfigureRoutes $r2): ConfigureRoutes {
                return $r2
                    ->delete('/delete', 'delete')
                    ->get('/get', 'get')
                    ->head('/head', 'head')
                    ->patch('/patch', 'patch')
                    ->post('/post', 'post')
                    ->put('/put', 'put')
                    ->options('/options', 'options');
            });
        });

        $immutable = $immutable->addGroup('/admin', static function (ConfigureRoutes $r): ConfigureRoutes {
            return $r->get('-some-info', 'admin-some-info');
        });

        $immutable = $immutable->addGroup('/admin-', static function (ConfigureRoutes $r): ConfigureRoutes {
            return $r->get('more-info', 'admin-more-info');
        });

        $expected = [
            ['DELETE', '/delete', 'delete', ['_route' => '/delete']],
            ['GET', '/get', 'get', ['_route' => '/get']],
            ['HEAD', '/head', 'head', ['_route' => '/head']],
            ['PATCH', '/patch', 'patch', ['_route' => '/patch']],
            ['POST', '/post', 'post', ['_route' => '/post']],
            ['PUT', '/put', 'put', ['_route' => '/put']],
            ['OPTIONS', '/options', 'options', ['_route' => '/options']],
            ['DELETE', '/group-one/delete', 'delete', ['_route' => '/group-one/delete']],
            ['GET', '/group-one/get', 'get', ['_route' => '/group-one/get']],
            ['HEAD', '/group-one/head', 'head', ['_route' => '/group-one/head']],
            ['PATCH', '/group-one/patch', 'patch', ['_route' => '/group-one/patch']],
            ['POST', '/group-one/post', 'post', ['_route' => '/group-one/post']],
            ['PUT', '/group-one/put', 'put', ['_route' => '/group-one/put']],
            ['OPTIONS', '/group-one/options', 'options', ['_route' => '/group-one/options']],
            ['DELETE', '/group-one/group-two/delete', 'delete', ['_route' => '/group-one/group-two/delete']],
            ['GET', '/group-one/group-two/get', 'get', ['_route' => '/group-one/group-two/get']],
            ['HEAD', '/group-one/group-two/head', 'head', ['_route' => '/group-one/group-two/head']],
            ['PATCH', '/group-one/group-two/patch', 'patch', ['_route' => '/group-one/group-two/patch']],
            ['POST', '/group-one/group-two/post', 'post', ['_route' => '/group-one/group-two/post']],
            ['PUT', '/group-one/group-two/put', 'put', ['_route' => '/group-one/group-two/put']],
            ['OPTIONS', '/group-one/group-two/options', 'options', ['_route' => '/group-one/group-two/options']],
            ['GET', '/admin-some-info', 'admin-some-info', ['_route' => '/admin-some-info']],
            ['GET', '/admin-more-info', 'admin-more-info', ['_route' => '/admin-more-info']],
        ];

        self::assertSame($expected, $immutable->processedRoutes()[0]);
    }

    #[PHPUnit\Test]
    public function namedRoutesShouldBeRegistered(): void
    {
        $r = self::routeCollector();

        $immutable = $r->get('/', 'index-handler', ['_name' => 'index']);
        $immutable = $immutable->get('/users/me', 'fetch-user-handler', ['_name' => 'users.fetch']);

        self::assertSame(['index' => [['/']], 'users.fetch' => [['/users/me']]], $immutable->processedRoutes()[2]);
    }

    #[PHPUnit\Test]
    public function cannotDefineRouteWithEmptyName(): void
    {
        $r = self::routeCollector();

        $this->expectException(BadRouteException::class);

        $r->get('/', 'index-handler', ['_name' => '']);
    }

    #[PHPUnit\Test]
    public function cannotDefineRouteWithInvalidTypeAsName(): void
    {
        $r = self::routeCollector();

        $this->expectException(BadRouteException::class);

        $r->get('/', 'index-handler', ['_name' => false]);
    }

    #[PHPUnit\Test]
    public function cannotDefineDuplicatedRouteName(): void
    {
        $r = self::routeCollector();

        $this->expectException(BadRouteException::class);

        $immutable = $r->get('/', 'index-handler', ['_name' => 'index']);
        $immutable->get('/users/me', 'fetch-user-handler', ['_name' => 'index']);
    }

    private static function routeCollector(): ConfigureRoutes
    {
        return new RouteCollectorImmutable(new Std(), self::dummyDataGenerator());
    }

    private static function dummyDataGenerator(): DataGenerator
    {
        return new class implements DataGenerator
        {
            /** @var list<array{string, string, mixed, array<string, bool|float|int|string>}> */
            private array $routes = [];

            /** @inheritDoc */
            public function getData(): array
            {
                return [$this->routes, []];
            }

            /** @inheritDoc */
            public function addRoute(string $httpMethod, array $routeData, mixed $handler, array $extraParameters = []): void
            {
                TestCase::assertTrue(count($routeData) === 1 && is_string($routeData[0]));

                $this->routes[] = [$httpMethod, $routeData[0], $handler, $extraParameters];
            }
        };
    }
}
