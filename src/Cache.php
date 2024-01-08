<?php
declare(strict_types=1);

namespace FastRoute;

/** @phpstan-import-type RouteData from DataGenerator */
interface Cache
{
    /**
     * @param callable():RouteData $loader
     *
     * @return RouteData
     */
    public function get(string $key, callable $loader): array;
}
