<?php
declare(strict_types=1);

namespace FastRoute;

use FastRoute\Cache\FileCache;
use LogicException;

use function function_exists;
use function is_string;

if (! function_exists('FastRoute\simpleDispatcher')) {
    /** @param array{routeParser?: class-string<RouteParser>, dataGenerator?: class-string<DataGenerator>, dispatcher?: class-string<Dispatcher>, routeCollector?: class-string<RouteCollector>, cacheDisabled?: bool, cacheKey?: string, cacheFile?: string, cacheDriver?: class-string<Cache>|Cache} $options */
    function simpleDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        return \FastRoute\cachedDispatcher(
            $routeDefinitionCallback,
            ['cacheDisabled' => true] + $options,
        );
    }

    /** @param array{routeParser?: class-string<RouteParser>, dataGenerator?: class-string<DataGenerator>, dispatcher?: class-string<Dispatcher>, routeCollector?: class-string<RouteCollector>, cacheDisabled?: bool, cacheKey?: string, cacheFile?: string, cacheDriver?: class-string<Cache>|Cache} $options */
    function cachedDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        $options += [
            'routeParser' => RouteParser\Std::class,
            'dataGenerator' => DataGenerator\MarkBased::class,
            'dispatcher' => Dispatcher\MarkBased::class,
            'routeCollector' => RouteCollector::class,
            'cacheDisabled' => false,
            'cacheDriver' => FileCache::class,
        ];

        $loader = static function () use ($routeDefinitionCallback, $options): array {
            $routeCollector = new $options['routeCollector'](
                new $options['routeParser'](),
                new $options['dataGenerator']()
            );

            $routeDefinitionCallback($routeCollector);

            return $routeCollector->getData();
        };

        if ($options['cacheDisabled'] === true) {
            return new $options['dispatcher']($loader());
        }

        $cacheKey = $options['cacheKey'] ?? $options['cacheFile'] ?? null;

        if ($cacheKey === null) {
            throw new LogicException('Must specify "cacheKey" option');
        }

        $cache = $options['cacheDriver'];

        if (is_string($cache)) {
            $cache = new $cache();
        }

        return new $options['dispatcher']($cache->get($cacheKey, $loader));
    }
}
