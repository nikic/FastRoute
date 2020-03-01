<?php
declare(strict_types=1);

namespace FastRoute;

use LogicException;
use RuntimeException;
use function assert;
use function file_exists;
use function file_put_contents;
use function function_exists;
use function is_array;
use function var_export;

if (! function_exists('FastRoute\simpleDispatcher')) {
    /**
     * @param array<string, string> $options
     */
    function simpleDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        return Functions::simpleDispatcher($routeDefinitionCallback, $options):
    }

    /**
     * @param array<string, string> $options
     */
    function cachedDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        return Functions::cachedDispatcher($routeDefinitionCallback, $options);
    }
}
