<?php
declare(strict_types=1);

namespace FastRoute\Benchmark;

use FastRoute\ConfigureRoutes;
use FastRoute\FastRoute;
use FastRoute\GenerateUri;
use PhpBench\Attributes as Bench;

/** @phpstan-import-type UriSubstitutions from GenerateUri */
#[Bench\Iterations(5)]
#[Bench\Revs(250)]
#[Bench\Warmup(3)]
#[Bench\BeforeMethods(['registerGenerator'])]
final class UriGenerationBench
{
    private GenerateUri $generator;

    public function registerGenerator(): void
    {
        $loader = static function (ConfigureRoutes $routes): void {
            $routes->addRoute('GET', '/', 'do-something', ['_name' => 'home']);
            $routes->addRoute('GET', '/page/{page_slug:[a-zA-Z0-9\-]+}', 'do-something', ['_name' => 'page.show']);
            $routes->addRoute('GET', '/about-us', 'do-something', ['_name' => 'about-us']);
            $routes->addRoute('GET', '/contact-us', 'do-something', ['_name' => 'contact-us']);
            $routes->addRoute('POST', '/contact-us', 'do-something', ['_name' => 'contact-us.submit']);
            $routes->addRoute('GET', '/blog', 'do-something', ['_name' => 'blog.index']);
            $routes->addRoute('GET', '/blog/recent', 'do-something', ['_name' => 'blog.recent']);
            $routes->addRoute('GET', '/blog/{year}[/{month}[/{day}]]', 'do-something', ['_name' => 'blog.archive']);
            $routes->addRoute('GET', '/blog/post/{post_slug:[a-zA-Z0-9\-]+}', 'do-something', ['_name' => 'blog.post.show']);
            $routes->addRoute('POST', '/blog/post/{post_slug:[a-zA-Z0-9\-]+}/comment', 'do-something', ['_name' => 'blog.post.comment']);
            $routes->addRoute('GET', '/shop', 'do-something', ['_name' => 'shop.index']);
            $routes->addRoute('GET', '/shop/category', 'do-something', ['_name' => 'shop.category.index']);
            $routes->addRoute('GET', '/shop/category/{category_id:\d+}/product/search/{filter_by:[a-zA-Z]+}:{filter_value}', 'do-something', ['_name' => 'shop.category.product.search']);
        };

        $this->generator = FastRoute::recommendedSettings($loader, 'cache')
            ->disableCache()
            ->uriGenerator();
    }

    /** @param array{name: non-empty-string} $params */
    #[Bench\Subject]
    #[Bench\ParamProviders(['allStaticRoutes'])]
    public function staticRoutes(array $params): void
    {
        $this->generator->forRoute($params['name']);
    }

    /** @return iterable<non-empty-string, array{name: non-empty-string}> */
    public static function allStaticRoutes(): iterable
    {
        $staticRoutes = [
            'home',
            'about-us',
            'contact-us',
            'contact-us.submit',
            'blog.index',
            'blog.recent',
            'shop.index',
            'shop.category.index',
        ];

        foreach ($staticRoutes as $route) {
            yield $route => ['name' => $route];
        }
    }

    /** @param array{name: non-empty-string, substitutions: UriSubstitutions} $params */
    #[Bench\Subject]
    #[Bench\ParamProviders(['allDynamicRoutes'])]
    public function dynamicRoutes(array $params): void
    {
        $this->generator->forRoute($params['name'], $params['substitutions']);
    }

    /** @return iterable<non-empty-string, array{name: non-empty-string, substitutions: UriSubstitutions}> */
    public static function allDynamicRoutes(): iterable
    {
        yield 'page.show' => ['name' => 'page.show', 'substitutions' => ['page_slug' => 'testing-one-two-three']];

        yield 'blog.post.show' => ['name' => 'blog.post.show', 'substitutions' => ['post_slug' => 'testing-one-two-three']];
        yield 'blog.post.comment' => ['name' => 'blog.post.comment', 'substitutions' => ['post_slug' => 'testing-one-two-three']];
        yield 'blog.archive-year' => ['name' => 'blog.archive', 'substitutions' => ['year' => '2014']];
        yield 'blog.archive-year-month' => ['name' => 'blog.archive', 'substitutions' => ['year' => '2014', 'month' => '03']];
        yield 'blog.archive-year-day' => ['name' => 'blog.archive', 'substitutions' => ['year' => '2014', 'month' => '03', 'day' => '15']];

        yield 'shop.category.product.search' => ['name' => 'shop.category.product.search', 'substitutions' => ['category_id' => '1', 'filter_by' => 'name', 'filter_value' => 'testing']];
    }
}
