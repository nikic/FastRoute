<?php

namespace FastRoute;

use FastRoute\DispatcherResult\FoundResult;
use FastRoute\DispatcherResult\MethodNotAllowedResult;
use FastRoute\DispatcherResult\NotFoundResult;

interface Dispatcher {
    /**
     * Dispatches against the provided HTTP method verb and URI.
     *
     * @param string $httpMethod
     * @param string $uri
     *
     * @return DispatcherResult|FoundResult|NotFoundResult|MethodNotAllowedResult
     */
    public function dispatch($httpMethod, $uri);
}
