<?php

namespace FastRoute\RouteParser;

use FastRoute\RouteParser;

/**
 * Parses routes of the following form:
 *
 * "/user/{name}/{id:[0-9]+}"
 */
class Std implements RouteParser {
    const VARIABLE_REGEX = <<<'REGEX'
~\{
    \s* ([a-zA-Z][a-zA-Z0-9_]*) \s*
    (?:
        : \s* ([^{}]*(?:\{(?-1)\}[^{}]*)*)
    )?
\}~x
REGEX;
    const DEFAULT_DISPATCH_REGEX = '[^/]+';

    public function parse($route) {
        if (!preg_match_all(
            self::VARIABLE_REGEX, $route, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        )) {
            return array($route);
        }

        $offset = 0;
        $routeData = array();
        foreach ($matches as $set) {
            if ($set[0][1] > $offset) {
                $routeData[] = substr($route, $offset, $set[0][1] - $offset);
            }
            $routeData[] = array(
                $set[1][0],
                isset($set[2]) ? trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX
            );
            $offset = $set[0][1] + strlen($set[0][0]);
        }

        if ($offset != strlen($route)) {
            $routeData[] = substr($route, $offset);
        }

        return $routeData;
    }
}
