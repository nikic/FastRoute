<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use PHPUnit\Framework\TestCase;

use function FastRoute\cachedDispatcher;
use function unlink;

final class CachingTest extends TestCase
{
    private const CACHE_FILE = __DIR__ . '/routing_cache.php';

    /** @after */
    public function cleanUpCache(): void
    {
        unlink(self::CACHE_FILE);
    }

    /** @before */
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
            ['cacheFile' => self::CACHE_FILE]
        );
    }

    /** @test */
    public function dynamicRouteShouldMatch(): void
    {
        $dispatcher = $this->createDispatcher();
        $result = $dispatcher->dispatch('GET', '/admin/1234');

        self::assertSame(Dispatcher::FOUND, $result[0]);
        self::assertSame(['admin-page'], $result[1]);
        self::assertSame(['page' => '1234'], $result[2]);
    }

    /** @test */
    public function staticRouteShouldMatch(): void
    {
        $dispatcher = $this->createDispatcher();
        $result = $dispatcher->dispatch('GET', '/testing');

        self::assertSame(Dispatcher::FOUND, $result[0]);
        self::assertSame(['test'], $result[1]);
    }

    /** @test */
    public function missingRoutShouldNotBeFound(): void
    {
        $dispatcher = $this->createDispatcher();
        $result = $dispatcher->dispatch('GET', '/testing2');

        self::assertSame(Dispatcher::NOT_FOUND, $result[0]);
    }
}
