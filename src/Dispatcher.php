<?php
declare(strict_types=1);

namespace FastRoute;

use FastRoute\Dispatcher\Result;

interface Dispatcher
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    /**
     * Dispatches against the provided HTTP method verb and URI.
     */
    public function dispatch(string $httpMethod, string $uri): Result;
}
