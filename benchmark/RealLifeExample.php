<?php
declare(strict_types=1);

namespace FastRoute\Benchmark;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

final class RealLifeExample extends Dispatching
{
    /**
     * {@inheritDoc}
     */
    protected function createDispatcher(array $options = []): Dispatcher
    {
        return simpleDispatcher(
            static function (RouteCollector $routes): void {
                $routes->addRoute('GET', '/', ['name' => 'home']);
                $routes->addRoute('GET', '/page/{page_slug:[a-zA-Z0-9\-]+}', ['name' => 'page.show']);
                $routes->addRoute('GET', '/about-us', ['name' => 'about-us']);
                $routes->addRoute('GET', '/contact-us', ['name' => 'contact-us']);
                $routes->addRoute('POST', '/contact-us', ['name' => 'contact-us.submit']);
                $routes->addRoute('GET', '/blog', ['name' => 'blog.index']);
                $routes->addRoute('GET', '/blog/recent', ['name' => 'blog.recent']);
                $routes->addRoute('GET', '/blog/post/{post_slug:[a-zA-Z0-9\-]+}', ['name' => 'blog.post.show']);
                $routes->addRoute('POST', '/blog/post/{post_slug:[a-zA-Z0-9\-]+}/comment', ['name' => 'blog.post.comment']);
                $routes->addRoute('GET', '/shop', ['name' => 'shop.index']);
                $routes->addRoute('GET', '/shop/category', ['name' => 'shop.category.index']);
                $routes->addRoute('GET', '/shop/category/search/{filter_by:[a-zA-Z]+}:{filter_value}', ['name' => 'shop.category.search']);
                $routes->addRoute('GET', '/shop/category/{category_id:\d+}', ['name' => 'shop.category.show']);
                $routes->addRoute('GET', '/shop/category/{category_id:\d+}/product', ['name' => 'shop.category.product.index']);
                $routes->addRoute('GET', '/shop/category/{category_id:\d+}/product/search/{filter_by:[a-zA-Z]+}:{filter_value}', ['name' => 'shop.category.product.search']);
                $routes->addRoute('GET', '/shop/product', ['name' => 'shop.product.index']);
                $routes->addRoute('GET', '/shop/product/search/{filter_by:[a-zA-Z]+}:{filter_value}', ['name' => 'shop.product.search']);
                $routes->addRoute('GET', '/shop/product/{product_id:\d+}', ['name' => 'shop.product.show']);
                $routes->addRoute('GET', '/shop/cart', ['name' => 'shop.cart.show']);
                $routes->addRoute('PUT', '/shop/cart', ['name' => 'shop.cart.add']);
                $routes->addRoute('DELETE', '/shop/cart', ['name' => 'shop.cart.empty']);
                $routes->addRoute('GET', '/shop/cart/checkout', ['name' => 'shop.cart.checkout.show']);
                $routes->addRoute('POST', '/shop/cart/checkout', ['name' => 'shop.cart.checkout.process']);
                $routes->addRoute('GET', '/admin/login', ['name' => 'admin.login']);
                $routes->addRoute('POST', '/admin/login', ['name' => 'admin.login.submit']);
                $routes->addRoute('GET', '/admin/logout', ['name' => 'admin.logout']);
                $routes->addRoute('GET', '/admin', ['name' => 'admin.index']);
                $routes->addRoute('GET', '/admin/product', ['name' => 'admin.product.index']);
                $routes->addRoute('GET', '/admin/product/create', ['name' => 'admin.product.create']);
                $routes->addRoute('POST', '/admin/product', ['name' => 'admin.product.store']);
                $routes->addRoute('GET', '/admin/product/{product_id:\d+}', ['name' => 'admin.product.show']);
                $routes->addRoute('GET', '/admin/product/{product_id:\d+}/edit', ['name' => 'admin.product.edit']);
                $routes->addRoute(['PUT', 'PATCH'], '/admin/product/{product_id:\d+}', ['name' => 'admin.product.update']);
                $routes->addRoute('DELETE', '/admin/product/{product_id:\d+}', ['name' => 'admin.product.destroy']);
                $routes->addRoute('GET', '/admin/category', ['name' => 'admin.category.index']);
                $routes->addRoute('GET', '/admin/category/create', ['name' => 'admin.category.create']);
                $routes->addRoute('POST', '/admin/category', ['name' => 'admin.category.store']);
                $routes->addRoute('GET', '/admin/category/{category_id:\d+}', ['name' => 'admin.category.show']);
                $routes->addRoute('GET', '/admin/category/{category_id:\d+}/edit', ['name' => 'admin.category.edit']);
                $routes->addRoute(['PUT', 'PATCH'], '/admin/category/{category_id:\d+}', ['name' => 'admin.category.update']);
                $routes->addRoute('DELETE', '/admin/category/{category_id:\d+}', ['name' => 'admin.category.destroy']);
            },
            $options
        );
    }

    /**
     * {@inheritDoc}
     */
    public function provideStaticRoutes(): iterable
    {
        yield 'first' => [
            'method' => 'GET',
            'route' => '/',
            'result' => [Dispatcher::FOUND, ['name' => 'home'], []],
        ];

        yield 'last' => [
            'method' => 'GET',
            'route' => '/admin/category',
            'result' => [Dispatcher::FOUND, ['name' => 'admin.category.index'], []],
        ];

        yield 'invalid-method' => [
            'method' => 'PUT',
            'route' => '/about-us',
            'result' => [Dispatcher::METHOD_NOT_ALLOWED, ['GET']],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideDynamicRoutes(): iterable
    {
        yield 'first' => [
            'method' => 'GET',
            'route' => '/page/hello-word',
            'result' => [Dispatcher::FOUND, ['name' => 'page.show'], ['page_slug' => 'hello-word']],
        ];

        yield 'last' => [
            'method' => 'GET',
            'route' => '/admin/category/123',
            'result' => [Dispatcher::FOUND, ['name' => 'admin.category.show'], ['category_id' => '123']],
        ];

        yield 'invalid-method' => [
            'method' => 'PATCH',
            'route' => '/shop/category/123',
            'result' => [Dispatcher::METHOD_NOT_ALLOWED, ['GET']],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideOtherScenarios(): iterable
    {
        yield 'non-existent' => [
            'method' => 'GET',
            'route' => '/shop/product/awesome',
            'result' => [Dispatcher::NOT_FOUND],
        ];

        yield 'longest-route' => [
            'method' => 'GET',
            'route' => '/shop/category/123/product/search/status:sale',
            'result' => [
                Dispatcher::FOUND,
                ['name' => 'shop.category.product.search'],
                ['category_id' => '123', 'filter_by' => 'status', 'filter_value' => 'sale'],
            ],
        ];
    }
}
