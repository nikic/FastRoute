<?php
declare(strict_types=1);

namespace FastRoute;

use function function_exists;

if (! function_exists('FastRoute\simpleDispatcher')) {
    /**
     * @param array<string, string> $options
     */
    function simpleDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        return Functions::simpleDispatcher($routeDefinitionCallback, $options);
    }

    /**
     * @param array<string, string> $options
     */
    function cachedDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        return Functions::cachedDispatcher($routeDefinitionCallback, $options);
    }
}
