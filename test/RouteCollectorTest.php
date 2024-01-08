<?php
declare(strict_types=1);

namespace FastRoute\Test;

use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class RouteCollectorTest extends TestCase
{
    #[PHPUnit\Test]
    public function shortcutsCanBeUsedToRegisterRoutes(): void
    {
        $r = new DummyRouteCollector();

        $r->any('/any', 'any');
        $r->delete('/delete', 'delete');
        $r->get('/get', 'get');
        $r->head('/head', 'head');
        $r->patch('/patch', 'patch');
        $r->post('/post', 'post');
        $r->put('/put', 'put');
        $r->options('/options', 'options');

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

        self::assertSame($expected, $r->routes);
    }

    #[PHPUnit\Test]
    public function routesCanBeGrouped(): void
    {
        $r = new DummyRouteCollector();

        $r->delete('/delete', 'delete');
        $r->get('/get', 'get');
        $r->head('/head', 'head');
        $r->patch('/patch', 'patch');
        $r->post('/post', 'post');
        $r->put('/put', 'put');
        $r->options('/options', 'options');

        $r->addGroup('/group-one', static function (DummyRouteCollector $r): void {
            $r->delete('/delete', 'delete');
            $r->get('/get', 'get');
            $r->head('/head', 'head');
            $r->patch('/patch', 'patch');
            $r->post('/post', 'post');
            $r->put('/put', 'put');
            $r->options('/options', 'options');

            $r->addGroup('/group-two', static function (DummyRouteCollector $r): void {
                $r->delete('/delete', 'delete');
                $r->get('/get', 'get');
                $r->head('/head', 'head');
                $r->patch('/patch', 'patch');
                $r->post('/post', 'post');
                $r->put('/put', 'put');
                $r->options('/options', 'options');
            });
        });

        $r->addGroup('/admin', static function (DummyRouteCollector $r): void {
            $r->get('-some-info', 'admin-some-info');
        });
        $r->addGroup('/admin-', static function (DummyRouteCollector $r): void {
            $r->get('more-info', 'admin-more-info');
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

        self::assertSame($expected, $r->routes);
    }
}
