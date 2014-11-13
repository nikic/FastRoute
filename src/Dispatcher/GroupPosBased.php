<?php

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher;

class GroupPosBased implements Dispatcher {
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
            return array(self::FOUND, $routes[$httpMethod], array());
        } elseif ($httpMethod === 'HEAD' && isset($routes['GET'])) {
            return array(self::FOUND, $routes['GET'], array());
        } else {
            return array(self::METHOD_NOT_ALLOWED, array_keys($routes));
        }
    }

    private function dispatchVariableRoute($httpMethod, $uri) {
        foreach ($this->variableRouteData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            // find first non-empty match
            for ($i = 1; '' === $matches[$i]; ++$i);

            $routes = $data['routeMap'][$i];
            if (!isset($routes[$httpMethod])) {
                if ($httpMethod === 'HEAD' && isset($routes['GET'])) {
                    $httpMethod = 'GET';
                } else {
                    return array(self::METHOD_NOT_ALLOWED, array_keys($routes));
                }
            }

            list($handler, $varNames) = $routes[$httpMethod];

            $vars = array();
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[$i++];
            }
            return array(self::FOUND, $handler, $vars);
        }

        return array(self::NOT_FOUND);
    }
}
