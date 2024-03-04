<?php
declare(strict_types=1);

namespace FastRoute\Test\Cache;

use FastRoute\Cache\Psr16Cache;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

final class Psr16CacheTest extends TestCase
{
    #[PHPUnit\Test]
    public function cacheShouldOnlyBeSetOnMiss(): void
    {
        $data = [];

        $generatedData = [['GET' => ['/' => ['test', []]]], [], []];

        $adapter = new Psr16Cache($this->createDummyCache($data));
        $result = $adapter->get('test', static fn () => $generatedData);

        self::assertSame($generatedData, $result);
        self::assertSame($generatedData, $data['test']);

        // Try again, now with a different callback
        $result = $adapter->get('test', static fn () => [['POST' => ['/' => ['test', []]]], [], []]);

        self::assertSame($generatedData, $result);
    }

    /** @param array<string, mixed> $data */
    private function createDummyCache(array &$data): CacheInterface
    {
        $cache = $this->createMock(CacheInterface::class);

        $cache->method('get')
            ->willReturnCallback(
                static function (string $key, mixed $default) use (&$data): mixed {
                    return $data[$key] ?? $default;
                },
            );

        $cache->method('set')
            ->willReturnCallback(
                static function (string $key, mixed $value) use (&$data): bool {
                    $data[$key] = $value;

                    return true;
                },
            );

        return $cache;
    }
}
