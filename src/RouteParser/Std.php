<?php

namespace FastRoute\RouteParser;

use FastRoute\BadRouteException;
use FastRoute\RouteParser;

/**
 * Parses route strings of the following form:
 *
 * "/user/{name}[/{id:[0-9]+}]"
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
        $routeWithoutClosingOptionals = rtrim($route, ']');
        $numOptionals = strlen($route) - strlen($routeWithoutClosingOptionals);
        $routeParts = $this->parsePlaceholders($routeWithoutClosingOptionals);
        if ($numOptionals === 0) {
            return [$routeParts];
        }
        return $this->handleOptionals($routeParts, $numOptionals);
    }

    private function handleOptionals($routeParts, $numOptionals) {
        $routeDatas = [];
        $currentRouteData = [];
        foreach ($routeParts as $part) {
            // skip placeholders
            if (!is_string($part)) {
                $currentRouteData[] = $part;
                continue;
            }

            $segments = explode('[', $part);
            $currentNumOptionals = count($segments) - 1;
            $numOptionals -= $currentNumOptionals;
            if ($numOptionals < 0) {
                throw new BadRouteException("Found more opening '[' than closing ']'");
            }

            $currentPart = '';
            foreach ($segments as $i => $addPart) {
                if ($addPart === '') {
                    if ($currentPart !== '') {
                        throw new BadRouteException("Empty optional part");
                    }
                    $routeDatas[] = $currentRouteData;
                    continue;
                }

                $currentPart .= $addPart;
                if ($i !== $currentNumOptionals) {
                    $routeData = $currentRouteData;
                    $routeData[] = $currentPart;
                    $routeDatas[] = $routeData;
                } else {
                    $currentRouteData[] = $currentPart;
                }
            }
        }

        $routeDatas[] = $currentRouteData;
        if ($numOptionals > 0) {
            throw new BadRouteException("Found more closing ']' than opening '['");
        }

        return $routeDatas;
    }

    /**
     * Parses a route string only considering {placeholders}, but ignoring [optionals].
     */
    private function parsePlaceholders($route) {
        if (!preg_match_all(
            self::VARIABLE_REGEX, $route, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER
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

        if ($offset != strlen($route)) {
            $routeData[] = substr($route, $offset);
        }

        return $routeData;
    }
}
