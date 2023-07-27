<?php
declare(strict_types=1);

namespace FastRoute;

interface DataGenerator
{
    /**
     * Adds a route to the data generator. The route data uses the
     * same format that is returned by RouterParser::parser().
     *
     * The handler doesn't necessarily need to be a callable, it
     * can be arbitrary data that will be returned when the route
     * matches.
     *
     * @param mixed[] $routeData
     */
    public function addRoute(string $httpMethod, array $routeData, mixed $handler): void;

    /**
     * Returns dispatcher data in some unspecified format, which
     * depends on the used method of dispatch.
     *
     * @return array{0: array<string, array<string, mixed>>, 1: array<string, array<array{regex: string, suffix?: string, routeMap: array<int|string, array{0: mixed, 1: array<string, string>}>}>>}
     */
    public function getData(): array;
}
