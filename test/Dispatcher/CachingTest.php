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

    public function createDispatcher(string $optionName = 'cacheKey'): Dispatcher
    {
        return cachedDispatcher(
            static function (RouteCollector $collector): void {
                $collector->get('/testing', ['test']);
                $collector->get('/admin/{page}', ['admin-page']);
            },
            // @phpstan-ignore-next-line we're doing dynamic configuration...
            [$optionName => self::CACHE_FILE],
        );
    }

    #[PHPUnit\Test]
    #[PHPUnit\DataProvider('possiblePropertyNames')]
    public function dynamicRouteShouldMatch(string $propertyName): void
    {
        $dispatcher = $this->createDispatcher($propertyName);
        $result = $dispatcher->dispatch('GET', '/admin/1234');

        self::assertSame(Dispatcher::FOUND, $result[0]);
        self::assertSame(['admin-page'], $result[1]);
        self::assertSame(['page' => '1234'], $result[2]);
    }

    #[PHPUnit\Test]
    #[PHPUnit\DataProvider('possiblePropertyNames')]
    public function staticRouteShouldMatch(string $propertyName): void
    {
        $dispatcher = $this->createDispatcher($propertyName);
        $result = $dispatcher->dispatch('GET', '/testing');

        self::assertSame(Dispatcher::FOUND, $result[0]);
        self::assertSame(['test'], $result[1]);
    }

    #[PHPUnit\Test]
    #[PHPUnit\DataProvider('possiblePropertyNames')]
    public function missingRoutShouldNotBeFound(string $propertyName): void
    {
        $dispatcher = $this->createDispatcher($propertyName);
        $result = $dispatcher->dispatch('GET', '/testing2');

        self::assertSame(Dispatcher::NOT_FOUND, $result[0]);
    }

    /** @return iterable<string, array{string}> */
    public static function possiblePropertyNames(): iterable
    {
        yield 'v1' => ['cacheFile'];
        yield 'v2' => ['cacheKey'];
    }
}
