<?php
declare(strict_types=1);

namespace FastRoute\Test;

use FastRoute\Cache;
use FastRoute\ConfigureRoutes;
use FastRoute\Dispatcher;
use FastRoute\FastRoute;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FastRouteTest extends TestCase
{
    #[PHPUnit\Test]
    public function markShouldBeTheDefaultDispatcher(): void
    {
        $dispatcher = FastRoute::recommendedSettings(self::routes(...), 'test')
            ->disableCache()
            ->dispatcher();

        self::assertInstanceOf(Dispatcher\MarkBased::class, $dispatcher);
    }

    #[PHPUnit\Test]
    public function canBeConfiguredToUseCharCountDispatcher(): void
    {
        $dispatcher = FastRoute::recommendedSettings(self::routes(...), 'test')
            ->disableCache()
            ->useCharCountDispatcher()
            ->dispatcher();

        self::assertInstanceOf(Dispatcher\CharCountBased::class, $dispatcher);
    }

    #[PHPUnit\Test]
    public function canBeConfiguredToUseGroupPosDispatcher(): void
    {
        $dispatcher = FastRoute::recommendedSettings(self::routes(...), 'test')
            ->disableCache()
            ->useGroupPosDispatcher()
            ->dispatcher();

        self::assertInstanceOf(Dispatcher\GroupPosBased::class, $dispatcher);
    }

    #[PHPUnit\Test]
    public function canBeConfiguredToUseGroupCountDispatcher(): void
    {
        $dispatcher = FastRoute::recommendedSettings(self::routes(...), 'test')
            ->disableCache()
            ->useGroupCountDispatcher()
            ->dispatcher();

        self::assertInstanceOf(Dispatcher\GroupCountBased::class, $dispatcher);
    }

    #[PHPUnit\Test]
    public function canBeConfiguredToUseMarkDispatcher(): void
    {
        $dispatcher = FastRoute::recommendedSettings(self::routes(...), 'test')
            ->disableCache()
            ->useCharCountDispatcher()
            ->useMarkDispatcher()
            ->dispatcher();

        self::assertInstanceOf(Dispatcher\MarkBased::class, $dispatcher);
    }

    #[PHPUnit\Test]
    public function canBeConfiguredToUseCustomCache(): void
    {
        $cache = new class () implements Cache {
            /** @inheritDoc */
            public function get(string $key, callable $loader): array
            {
                if ($key === 'test') {
                    return [['GET' => ['/' => ['test2', ['test' => true]]]], [], []];
                }

                throw new RuntimeException('This dummy implementation is not meant for other cases');
            }
        };

        $dispatcher = FastRoute::recommendedSettings(self::routes(...), 'test2')
            ->withCache($cache, 'test')
            ->dispatcher();

        $result = $dispatcher->dispatch('GET', '/');

        self::assertInstanceOf(Dispatcher\Result\Matched::class, $result);
        self::assertSame('test2', $result->handler); // should use data from cache, not from loader
        self::assertSame(['test' => true], $result->extraParameters); // should use data from cache, not from loader
    }

    private static function routes(ConfigureRoutes $collector): void
    {
        $collector->get('/', 'test');
    }
}
