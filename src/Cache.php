<?php
declare(strict_types=1);

namespace FastRoute;

interface Cache
{
    /**
     * @param callable():array{0: array<string, array<string, mixed>>, 1: array<string, array<array{regex: string, suffix?: string, routeMap: array<int|string, array{0: mixed, 1: array<string, string>}>}>>} $loader
     *
     * @return array{0: array<string, array<string, mixed>>, 1: array<string, array<array{regex: string, suffix?: string, routeMap: array<int|string, array{0: mixed, 1: array<string, string>}>}>>}
     */
    public function get(string $key, callable $loader): array;
}
