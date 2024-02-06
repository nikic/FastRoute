<?php
declare(strict_types=1);

namespace FastRoute;

/**
 * @phpstan-type ParsedRoute array<string|array{string, string}>
 * @phpstan-type ParsedRoutes list<ParsedRoute>
 */
interface RouteParser
{
    /**
     * Parses a route string into multiple route data arrays.
     *
     * The expected output is defined using an example:
     *
     * For the route string "/fixedRoutePart/{varName}[/moreFixed/{varName2:\d+}]", if {varName} is interpreted as
     * a placeholder and [...] is interpreted as an optional route part, the expected result is:
     *
     * [
     *     // first route: without optional part
     *     [
     *         "/fixedRoutePart/",
     *         ["varName", "[^/]+"],
     *     ],
     *     // second route: with optional part
     *     [
     *         "/fixedRoutePart/",
     *         ["varName", "[^/]+"],
     *         "/moreFixed/",
     *         ["varName2", [0-9]+"],
     *     ],
     * ]
     *
     * Here one route string was converted into two route data arrays.
     *
     * @param string $route Route string to parse
     *
     * @return ParsedRoutes Array of route data arrays
     */
    public function parse(string $route): array;
}
