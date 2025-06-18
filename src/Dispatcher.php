<?php
declare(strict_types=1);

namespace FastRoute;

use FastRoute\Dispatcher\Result\Matched;
use FastRoute\Dispatcher\Result\MethodNotAllowed;
use FastRoute\Dispatcher\Result\NotMatched;

/** @phpstan-import-type ParsedRoutes from RouteParser */
interface Dispatcher
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    /** @param ParsedRoutes $processedData */
    public function with(array $processedData): self;

    /**
     * Dispatches against the provided HTTP method verb and URI.
     *
     * Returns an object that also has an array shape with one of the following formats:
     *
     *     [self::NOT_FOUND]
     *     [self::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
     *     [self::FOUND, $handler, ['varName' => 'value', ...]]
     */
    public function dispatch(string $httpMethod, string $uri): Matched|NotMatched|MethodNotAllowed;
}
