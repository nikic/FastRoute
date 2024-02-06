<?php
declare(strict_types=1);

namespace FastRoute;

/**
 * @phpstan-import-type ParsedRoute from RouteParser
 * @phpstan-type ExtraParameters array<string, string|int|bool|float>
 * @phpstan-type StaticRoutes array<string, array<string, array{mixed, ExtraParameters}>>
 * @phpstan-type DynamicRouteChunk array{regex: string, suffix?: string, routeMap: array<int|string, array{mixed, array<string, string>, ExtraParameters}>}
 * @phpstan-type DynamicRouteChunks list<DynamicRouteChunk>
 * @phpstan-type DynamicRoutes array<string, DynamicRouteChunks>
 * @phpstan-type RouteData array{StaticRoutes, DynamicRoutes}
 */
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
     * @param ParsedRoute     $routeData
     * @param ExtraParameters $extraParameters
     */
    public function addRoute(string $httpMethod, array $routeData, mixed $handler, array $extraParameters = []): void;

    /**
     * Returns dispatcher data in some unspecified format, which
     * depends on the used method of dispatch.
     *
     * @return RouteData
     */
    public function getData(): array;
}
