<?php
declare(strict_types=1);

namespace FastRoute;

use FastRoute\Cache\FileCache;
use LogicException;

use function function_exists;
use function is_string;

if (! function_exists('FastRoute\simpleDispatcher')) {
    /**
     * @deprecated since v2.0 and will be removed in v3.0
     *
     * @see FastRoute::recommendedSettings()
     * @see FastRoute::disableCache()
     *
     * @param callable(ConfigureRoutes):void                                                                                                                                                                                                                                                           $routeDefinitionCallback
     * @param array{routeParser?: class-string<RouteParser>, dataGenerator?: class-string<DataGenerator>, dispatcher?: class-string<Dispatcher>, routeCollector?: class-string<ConfigureRoutes>, cacheDisabled?: bool, cacheKey?: string, cacheFile?: string, cacheDriver?: class-string<Cache>|Cache} $options
     */
    function simpleDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        return \FastRoute\cachedDispatcher(
            $routeDefinitionCallback,
            ['cacheDisabled' => true] + $options,
        );
    }

    /**
     * @deprecated since v2.0 and will be removed in v3.0
     *
     * @see FastRoute::recommendedSettings()
     *
     * @param callable(ConfigureRoutes):void                                                                                                                                                                                                                                                           $routeDefinitionCallback
     * @param array{routeParser?: class-string<RouteParser>, dataGenerator?: class-string<DataGenerator>, dispatcher?: class-string<Dispatcher>, routeCollector?: class-string<ConfigureRoutes>, cacheDisabled?: bool, cacheKey?: string, cacheFile?: string, cacheDriver?: class-string<Cache>|Cache} $options
     */
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

            return $routeCollector->processedRoutes();
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
