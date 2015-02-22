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
        if (count($varRouteData) == 0) {
            return [self::NOT_FOUND];
        }

        if (!isset($varRouteData[$httpMethod])) {
            $httpMethod = $this->checkFallbacks($varRouteData, $httpMethod);
        }

        if (count($varRouteData) && $httpMethod === NULL) {
            return [self::METHOD_NOT_ALLOWED, $this->getAllowedMethods($varRouteData, $uri)];
        }

        $result = $this->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
        if ($result[0] === self::FOUND) {
            return $result;
        }

        return [self::NOT_FOUND];
    }

    protected function dispatchStaticRoute($httpMethod, $uri) {
        $routes = $this->staticRouteMap[$uri];
        if (!isset($routes[$httpMethod])) {
            $httpMethod = $this->checkFallbacks($routes, $httpMethod);
        }

        if ($httpMethod === NULL) {
            return [self::METHOD_NOT_ALLOWED, array_keys($routes)];
        }

        return [self::FOUND, $routes[$httpMethod], []];
    }

    protected function getAllowedMethods($varRouteData, $uri)
    {
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

        return $allowedMethods;
    }

    protected function checkFallbacks($routes, $httpMethod)
    {
        $additional = ['ANY'];

        if($httpMethod == 'HEAD') {
            $additional[] = 'GET';
        }

        foreach($additional as $method) {
            if(isset($routes[$method])) {
                return $method;
            }
        }

        return NULL;
    }
}
