<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

use function FastRoute\cachedDispatcher;
use function unlink;

final class CachingTest extends TestCase
{
    private const CACHE_FILE = __DIR__ . '/routing_cache.php';

    #[PHPUnit\After]
    public function cleanUpCache(): void
    {
        unlink(self::CACHE_FILE);
    }

    #[PHPUnit\Before]
    public function warmUpCache(): void
    {
        $this->createDispatcher();
    }

    public function createDispatcher(): Dispatcher
    {
        return cachedDispatcher(
            static function (RouteCollector $collector): void {
                $collector->get('/testing', ['test']);
                $collector->get('/admin/{page}', ['admin-page']);
            },
            ['cacheKey' => self::CACHE_FILE],
        );
    }

    #[PHPUnit\Test]
    public function dynamicRouteShouldMatch(): void
    {
        $dispatcher = $this->createDispatcher();
        $result = $dispatcher->dispatch('GET', '/admin/1234');

        self::assertSame(Dispatcher::FOUND, $result[0]);
        self::assertSame(['admin-page'], $result[1]);
        self::assertSame(['page' => '1234'], $result[2]);
    }

    #[PHPUnit\Test]
    public function staticRouteShouldMatch(): void
    {
        $dispatcher = $this->createDispatcher();
        $result = $dispatcher->dispatch('GET', '/testing');

        self::assertSame(Dispatcher::FOUND, $result[0]);
        self::assertSame(['test'], $result[1]);
    }

    #[PHPUnit\Test]
    public function missingRoutShouldNotBeFound(): void
    {
        $dispatcher = $this->createDispatcher();
        $result = $dispatcher->dispatch('GET', '/testing2');

        self::assertSame(Dispatcher::NOT_FOUND, $result[0]);
    }
}
