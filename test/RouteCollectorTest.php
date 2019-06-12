<?php
declare(strict_types=1);

namespace FastRoute\Test;

use PHPUnit\Framework\TestCase;

class RouteCollectorTest extends TestCase
{
    public function testShortcuts(): void
    {
        $r = new DummyRouteCollector();

        $r->delete('/delete', 'delete');
        $r->get('/get', 'get');
        $r->head('/head', 'head');
        $r->patch('/patch', 'patch');
        $r->post('/post', 'post');
        $r->put('/put', 'put');
        $r->options('/options', 'options');

        $expected = [
            ['DELETE', '/delete', 'delete'],
            ['GET', '/get', 'get'],
            ['HEAD', '/head', 'head'],
            ['PATCH', '/patch', 'patch'],
            ['POST', '/post', 'post'],
            ['PUT', '/put', 'put'],
            ['OPTIONS', '/options', 'options'],
        ];

        self::assertSame($expected, $r->routes);
    }

    public function testGroups(): void
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
            ['DELETE', '/delete', 'delete'],
            ['GET', '/get', 'get'],
            ['HEAD', '/head', 'head'],
            ['PATCH', '/patch', 'patch'],
            ['POST', '/post', 'post'],
            ['PUT', '/put', 'put'],
            ['OPTIONS', '/options', 'options'],
            ['DELETE', '/group-one/delete', 'delete'],
            ['GET', '/group-one/get', 'get'],
            ['HEAD', '/group-one/head', 'head'],
            ['PATCH', '/group-one/patch', 'patch'],
            ['POST', '/group-one/post', 'post'],
            ['PUT', '/group-one/put', 'put'],
            ['OPTIONS', '/group-one/options', 'options'],
            ['DELETE', '/group-one/group-two/delete', 'delete'],
            ['GET', '/group-one/group-two/get', 'get'],
            ['HEAD', '/group-one/group-two/head', 'head'],
            ['PATCH', '/group-one/group-two/patch', 'patch'],
            ['POST', '/group-one/group-two/post', 'post'],
            ['PUT', '/group-one/group-two/put', 'put'],
            ['OPTIONS', '/group-one/group-two/options', 'options'],
            ['GET', '/admin-some-info', 'admin-some-info'],
            ['GET', '/admin-more-info', 'admin-more-info'],
        ];

        self::assertSame($expected, $r->routes);
    }
}
