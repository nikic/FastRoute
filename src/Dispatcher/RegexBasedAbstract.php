<?php

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher;
use FastRoute\Exception\HttpMethodNotAllowedException;
use FastRoute\Exception\HttpNotFoundException;

abstract class RegexBasedAbstract implements Dispatcher {
    protected $staticRouteMap;
    protected $variableRouteData;

    protected abstract function dispatchVariableRoute($routeData, $uri);

    public function dispatch($httpMethod, $uri) {
        if (isset($this->staticRouteMap[$httpMethod][$uri])) {
            return $this->staticRouteMap[$httpMethod][$uri];
        }

        $varRouteData = $this->variableRouteData;
        if (isset($varRouteData[$httpMethod])) {
            return $this->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
        }

        // For HEAD requests, attempt fallback to GET
        if ($httpMethod === 'HEAD') {
            if (isset($this->staticRouteMap['GET'][$uri])) {
                $route = $this->staticRouteMap['GET'][$uri];
                return $route;
            }
            if (isset($varRouteData['GET'])) {
                return $this->dispatchVariableRoute($varRouteData['GET'], $uri);
            }
        }

        // If nothing else matches, try fallback routes
        if (isset($this->staticRouteMap['*'][$uri])) {
            return $this->staticRouteMap['*'][$uri];
        }
        if (isset($varRouteData['*'])) {
            return $this->dispatchVariableRoute($varRouteData['*'], $uri);
        }

        // Find allowed methods for this URI by matching against all other HTTP methods as well
        $allowedMethods = [];

        foreach ($this->staticRouteMap as $method => $uriMap) {
            if ($method !== $httpMethod && isset($uriMap[$uri])) {
                $allowedMethods[] = $method;
            }
        }

        foreach ($varRouteData as $method => $routeData) {
            if ($method === $httpMethod) {
                continue;
            }

            try {
                $route = $this->dispatchVariableRoute($routeData, $uri);
                $allowedMethods[] = $route->httpMethod;
            } catch (\Exception $e) {}
            
        }

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods) {
            throw new HttpMethodNotAllowedException($allowedMethods);
        } else {
            throw new HttpNotFoundException;
        }
    }
}
