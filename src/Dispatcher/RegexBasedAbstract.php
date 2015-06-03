<?php

namespace FastRoute\Dispatcher;

use FastRoute\DispatcherResult\FoundResult;
use FastRoute\DispatcherResult\MethodNotAllowedResult;
use FastRoute\DispatcherResult\NotFoundResult;
use FastRoute\Dispatcher;

abstract class RegexBasedAbstract implements Dispatcher {
    protected $staticRouteMap;
    protected $variableRouteData;

    public function dispatch($httpMethod, $uri) {
        if (isset($this->staticRouteMap[$httpMethod][$uri])) {
            $handler = $this->staticRouteMap[$httpMethod][$uri];
            return new FoundResult($handler, []);
        } elseif ($httpMethod === 'HEAD' && isset($this->staticRouteMap['GET'][$uri])) {
            $handler = $this->staticRouteMap['GET'][$uri];
            return new FoundResult($handler, []);
        }

        $varRouteData = $this->variableRouteData;
        if (isset($varRouteData[$httpMethod])) {
            $result = $this->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
            if ($result instanceof FoundResult) {
                return $result;
            }
        } elseif ($httpMethod === 'HEAD' && isset($varRouteData['GET'])) {
            $result = $this->dispatchVariableRoute($varRouteData['GET'], $uri);
            if ($result instanceof FoundResult) {
                return $result;
            }
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

            $result = $this->dispatchVariableRoute($routeData, $uri);
            if ($result instanceof FoundResult) {
                $allowedMethods[] = $method;
            }
        }

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods) {
            return new MethodNotAllowedResult($allowedMethods);
        } else {
            return new NotFoundResult();
        }
    }

    protected abstract function dispatchVariableRoute($routeData, $uri);
}
