<?php
declare(strict_types=1);

namespace FastRoute\Cache;

use FastRoute\Cache;
use Psr\SimpleCache\CacheInterface;

use function is_array;

final class Psr16Cache implements Cache
{
    public function __construct(private readonly CacheInterface $cache)
    {
    }

    /** @inheritDoc */
    public function get(string $key, callable $loader): array
    {
        $result = $this->cache->get($key);

        if (is_array($result)) {
            // @phpstan-ignore-next-line because we won´t be able to validate the array shape in a performant way
            return $result;
        }

        $data = $loader();
        $this->cache->set($key, $data);

        return $data;
    }
}
