<?php

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher;

abstract class RegexBasedAbstract implements Dispatcher {
    protected $staticRouteMap;
    protected $variableRouteData;

    protected abstract function dispatchVariableRoute($routeData, $uri);

    public function dispatch($httpMethod, $uri) {
        if (isset($this->staticRouteMap[$uri])) {
            return $this->dispatchStaticRoute($httpMethod, $uri);
        }

        $varRouteData = $this->variableRouteData;
        if (isset($varRouteData[$httpMethod])) {
            $result = $this->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
            if ($result[0] === self::FOUND) {
                return $result;
            }
        } else if ($httpMethod === 'HEAD' && isset($varRouteData['GET'])) {
            $result = $this->dispatchVariableRoute($varRouteData['GET'], $uri);
            if ($result[0] === self::FOUND) {
                return $result;
            }
        }

        // Find allowed methods for this URI by matching against all other
        // HTTP methods as well
        $allowedMethods = [];
        foreach ($varRouteData as $method => $routeData) {
            if ($method === $httpMethod) {
                continue;
            }

            $result = $this->dispatchVariableRoute($routeData, $uri);
            if ($result[0] === self::FOUND) {
                $allowedMethods[] = $method;
            }
        }

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods) {
            return [self::METHOD_NOT_ALLOWED, $allowedMethods];
        } else {
            return [self::NOT_FOUND];
        }
    }

    protected function dispatchStaticRoute($httpMethod, $uri) {
        $routes = $this->staticRouteMap[$uri];

        if (isset($routes[$httpMethod])) {
            return [self::FOUND, $routes[$httpMethod], []];
        } elseif ($httpMethod === 'HEAD' && isset($routes['GET'])) {
            return [self::FOUND, $routes['GET'], []];
        } else {
            return [self::METHOD_NOT_ALLOWED, array_keys($routes)];
        }
    }
}
