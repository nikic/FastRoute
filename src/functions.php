<?php
declare(strict_types=1);

namespace FastRoute;

use FastRoute\Factory\CachedDispatcherFactory;
use FastRoute\Factory\SimpleDispatcherFactory;

use function function_exists;

if (! function_exists('FastRoute\simpleDispatcher')) {
    /**
     * @param array<string, string> $options
     */
    function simpleDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        return SimpleDispatcherFactory::make($routeDefinitionCallback, $options);
    }

    /**
     * @param array<string, string> $options
     */
    function cachedDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        return CachedDispatcherFactory::make($routeDefinitionCallback, $options);
    }
}
