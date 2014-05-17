<?php

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher;

class MarkBased implements Dispatcher {
    private $staticRouteMap;
    private $variableRouteData;

    public function __construct($data) {
        list($this->staticRouteMap, $this->variableRouteData) = $data;
    }

    public function dispatch($httpMethod, $uri) {
        if (isset($this->staticRouteMap[$uri])) {
            return $this->dispatchStaticRoute($httpMethod, $uri);
        } else {
            return $this->dispatchVariableRoute($httpMethod, $uri);
        }
    }

    private function dispatchStaticRoute($httpMethod, $uri) {
        $routes = $this->staticRouteMap[$uri];

        if (isset($routes[$httpMethod])) {
            return [self::FOUND, $routes[$httpMethod], []];
        } elseif ($httpMethod === 'HEAD' && isset($routes['GET'])) {
            return [self::FOUND, $routes['GET'], []];
        } else {
            return [self::METHOD_NOT_ALLOWED, array_keys($routes)];
        }
    }

    private function dispatchVariableRoute($httpMethod, $uri) {
        foreach ($this->variableRouteData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            $routes = $data['routeMap'][$matches['MARK']];
            if (!isset($routes[$httpMethod])) {
                if ($httpMethod === 'HEAD' && isset($routes['GET'])) {
                    $httpMethod = 'GET';
                } else {
                    return [self::METHOD_NOT_ALLOWED, array_keys($routes)];
                }
            }

            list($handler, $varNames) = $routes[$httpMethod];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }
            return [self::FOUND, $handler, $vars];
        }

        return [self::NOT_FOUND];
    }
}
