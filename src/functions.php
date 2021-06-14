<?php
declare(strict_types=1);

namespace FastRoute;

use FastRoute\Cache\FileCache;
use LogicException;

use function array_key_exists;
use function assert;
use function function_exists;
use function is_string;

if (! function_exists('FastRoute\simpleDispatcher')) {
    /** @param array{routeParser?: string, dataGenerator?: string, dispatcher?: string, routeCollector?: string, cacheDisabled?: bool, cacheKey?: string, cacheDriver?: string|Cache} $options */
    function simpleDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        return \FastRoute\cachedDispatcher(
            $routeDefinitionCallback,
            ['cacheDisabled' => true] + $options
        );
    }

    /** @param array{routeParser?: string, dataGenerator?: string, dispatcher?: string, routeCollector?: string, cacheDisabled?: bool, cacheKey?: string, cacheDriver?: string|Cache} $options */
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
            assert($routeCollector instanceof RouteCollector);
            $routeDefinitionCallback($routeCollector);

            return $routeCollector->getData();
        };

        if ($options['cacheDisabled'] === true) {
            return new $options['dispatcher']($loader());
        }

        if (! array_key_exists('cacheKey', $options)) {
            throw new LogicException('Must specify "cacheKey" option');
        }

        $cache = $options['cacheDriver'];

        if (is_string($cache)) {
            $cache = new $cache();
        }

        assert($cache instanceof Cache);

        return new $options['dispatcher']($cache->get($options['cacheKey'], $loader));
    }
}
