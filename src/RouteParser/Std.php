<?php

namespace FastRoute\RouteParser;

use FastRoute\BadRouteException;
use FastRoute\RouteParser;

/**
 * Parses route strings of the following form:
 *
 * "/user/{name}[/{id:[0-9]+}]"
 */
class Std implements RouteParser
{
    const VARIABLE_REGEX = <<<'REGEX'
\{
    \s* ([a-zA-Z_][a-zA-Z0-9_-]*) \s*
    (?:
        : \s* ([^{}]*(?:\{(?-1)\}[^{}]*)*)
    )?
\}
REGEX;
    const DEFAULT_DISPATCH_REGEX = '[^/]+';

    public function parse($route)
    {
        if (strcspn($route, '[]') === strlen($route)) {
            return [$this->parsePlaceholders($route)];
        }

        $routeDatas = $this->consume($route);

        return array_map([$this, 'parsePlaceholders'], $routeDatas);
    }

    private function consume(& $route, $recursion = false)
    {
        $routeDatas = [''];

        do {
            $segments = preg_split(
                '~' . self::VARIABLE_REGEX . '(*SKIP)(*F) | ( \[ | ] )~x',
                $route,
                2
            );

            foreach ($routeDatas as $key => $data) {
                $routeDatas[$key] = $data . $segments[0];
            }

            if (isset($segments[1])) {
                $delimiter = $route[strlen($segments[0])];
                $route = $segments[1];

                if ($delimiter === ']') {
                    if (!$recursion) {
                        throw new BadRouteException("Number of opening '[' and closing ']' does not match");
                    }

                    if (in_array('', $routeDatas, true)) {
                        throw new BadRouteException('Empty optional part');
                    }

                    return $routeDatas;
                }

                $forks = $this->consume($route, true);

                foreach ($routeDatas as $data) {
                    foreach ($forks as $fork) {
                        $routeDatas[] = $data . $fork;
                    }
                }
            } else {
                $route = '';
            }
        } while (isset($segments[1]));

        if ($recursion) {
            throw new BadRouteException("Number of opening '[' and closing ']' does not match");
        }

        return $routeDatas;
    }

    /**
     * Parses a route string that does not contain optional segments.
     *
     * @param string
     * @return mixed[]
     */
    private function parsePlaceholders($route)
    {
        if (!preg_match_all(
            '~' . self::VARIABLE_REGEX . '~x', $route, $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        )) {
            return [$route];
        }

        $offset = 0;
        $routeData = [];
        foreach ($matches as $set) {
            if ($set[0][1] > $offset) {
                $routeData[] = substr($route, $offset, $set[0][1] - $offset);
            }
            $routeData[] = [
                $set[1][0],
                isset($set[2]) ? trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX
            ];
            $offset = $set[0][1] + strlen($set[0][0]);
        }

        if ($offset !== strlen($route)) {
            $routeData[] = substr($route, $offset);
        }

        return $routeData;
    }
}
