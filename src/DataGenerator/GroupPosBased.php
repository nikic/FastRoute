<?php

namespace FastRoute\DataGenerator;

use FastRoute\BadRouteException;
use FastRoute\DataGenerator;
use FastRoute\Route;

class GroupPosBased implements DataGenerator {
    const APPROX_CHUNK_SIZE = 10;

    private $staticRoutes = [];
    private $regexToRoutesMap = [];

    public function addRoute($httpMethod, $routeData, $handler) {
        if ($this->isStaticRoute($routeData)) {
            $this->staticRoutes[$routeData[0]][$httpMethod] = $handler;
        } else {
            $this->addVariableRoute($httpMethod, $routeData, $handler);
        }
    }

    private function isStaticRoute($routeData) {
        return count($routeData) == 1 && is_string($routeData[0]);
    }

    private function addVariableRoute($httpMethod, $routeData, $handler) {
        list($regex, $variables) = $this->buildRegexForRoute($routeData);

        $this->regexToRoutesMap[$regex][] = new Route(
            $httpMethod, $handler, $regex, $variables
        );
    }

    private function buildRegexForRoute($routeData) {
        $regex = '';
        $variables = [];
        foreach ($routeData as $part) {
            if (is_string($part)) {
                $regex .= preg_quote($part, '~');
                continue;
            }

            list($varName, $regexPart) = $part;

            if (isset($variables[$varName])) {
                throw new BadRouteException(sprintf(
                    'Route cannot use the same variable "%s" twice', $varName
                ));
            }

            $variables[$varName] = $varName;
            $regex .= '(' . $regexPart . ')';
        }

        return [$regex, $variables];
    }

    public function getData() {
        if (empty($this->regexToRoutesMap)) {
            return [$this->staticRoutes, [], []];
        }

        list($variableRoutes, $regexes) = $this->generateVariableRouteData();
        return [$this->staticRoutes, $variableRoutes, $regexes];
    }

    private function generateVariableRouteData() {
        $variableRoutes = [];
        $regexes = [];

        $chunkSize = $this->computeChunkSize(count($this->regexToRoutesMap));
        $chunks = array_chunk($this->regexToRoutesMap, $chunkSize, true);
        foreach ($chunks as $regexToRoutesMap) {
            list($curVariableRoutes, $curRegex) = $this->processChunk($regexToRoutesMap);

            $variableRoutes[] = $curVariableRoutes;
            $regexes[] = $curRegex;
        }

        return [$variableRoutes, $regexes];
    }

    private function computeChunkSize($count) {
        $numParts = max(1, round($count / self::APPROX_CHUNK_SIZE));
        return ceil($count / $numParts);
    }

    private function processChunk($regexToRoutesMap) {
        $variableRoutes = [];
        $regexes = [];
        $offset = 1;
        foreach ($regexToRoutesMap as $regex => $routes) {
            foreach ($routes as $route) {
                $variableRoutes[$offset][$route->httpMethod]
                    = [$route->handler, $route->variables];
            }

            $regexes[] = $regex;
            $offset += count($routes[0]->variables);
        }

        $regex = '~^(?:' . implode('|', $regexes) . ')$~';
        return [$variableRoutes, $regex];
    }
}

