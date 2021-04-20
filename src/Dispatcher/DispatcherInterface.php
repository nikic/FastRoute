<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

interface DispatcherInterface
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    /**
     * @param string $httpMethod HTTP Method
     * @param string $uri        URI
     */
    public function dispatch(string $httpMethod, string $uri): Result;
}
