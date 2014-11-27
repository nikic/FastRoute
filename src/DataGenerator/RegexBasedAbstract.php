<?php

namespace FastRoute\DataGenerator;

use FastRoute\DataGenerator;
use FastRoute\BadRouteException;
use FastRoute\Route;

abstract class RegexBasedAbstract implements DataGenerator {
    protected $staticRoutes = array();
    protected $methodToRegexToRoutesMap = array();

    protected abstract function getApproxChunkSize();
    protected abstract function processChunk($regexToRoutesMap);

    public function addRoute($httpMethod, $routeData, $handler) {
        if ($this->isStaticRoute($routeData)) {
            $this->addStaticRoute($httpMethod, $routeData, $handler);
        } else {
            $this->addVariableRoute($httpMethod, $routeData, $handler);
        }
    }

    public function getData() {
        if (empty($this->methodToRegexToRoutesMap)) {
            return array($this->staticRoutes, array());
        }

        return array($this->staticRoutes, $this->generateVariableRouteData());
    }

    private function generateVariableRouteData() {
        $data = array();
        foreach ($this->methodToRegexToRoutesMap as $method => $regexToRoutesMap) {
            $chunkSize = $this->computeChunkSize(count($regexToRoutesMap));
            $chunks = array_chunk($regexToRoutesMap, $chunkSize, true);
            $data[$method] =  array_map(array($this, 'processChunk'), $chunks);
        }
        return $data;
    }

    private function computeChunkSize($count) {
        $numParts = max(1, round($count / $this->getApproxChunkSize()));
        return ceil($count / $numParts);
    }

    private function isStaticRoute($routeData) {
        return count($routeData) == 1 && is_string($routeData[0]);
    }

    private function addStaticRoute($httpMethod, $routeData, $handler) {
        $routeStr = $routeData[0];

        if (isset($this->staticRoutes[$routeStr][$httpMethod])) {
            throw new BadRouteException(sprintf(
                'Cannot register two routes matching "%s" for method "%s"',
                $routeStr, $httpMethod
            ));
        }

        if (isset($this->methodToRegexToRoutesMap[$httpMethod])) {
            foreach ($this->methodToRegexToRoutesMap[$httpMethod] as $route) {
                if ($route->matches($routeStr)) {
                    throw new BadRouteException(sprintf(
                        'Static route "%s" is shadowed by previously defined variable route "%s" for method "%s"',
                        $routeStr, $route->regex, $httpMethod
                    ));
                }
            }
        }

        $this->staticRoutes[$routeStr][$httpMethod] = $handler;
    }

    private function addVariableRoute($httpMethod, $routeData, $handler) {
        list($regex, $variables) = $this->buildRegexForRoute($routeData);

        if (isset($this->methodToRegexToRoutesMap[$httpMethod][$regex])) {
            throw new BadRouteException(sprintf(
                'Cannot register two routes matching "%s" for method "%s"',
                $regex, $httpMethod
            ));
        }

        $this->methodToRegexToRoutesMap[$httpMethod][$regex] = new Route(
            $httpMethod, $handler, $regex, $variables
        );
    }

    private function buildRegexForRoute($routeData) {
        $regex = '';
        $variables = array();
        foreach ($routeData as $part) {
            if (is_string($part)) {
                $regex .= preg_quote($part, '~');
                continue;
            }

            list($varName, $regexPart) = $part;

            if (isset($variables[$varName])) {
                throw new BadRouteException(sprintf(
                    'Cannot use the same placeholder "%s" twice', $varName
                ));
            }

            $variables[$varName] = $varName;
            $regex .= '(' . $regexPart . ')';
        }

        return array($regex, $variables);
    }
}
