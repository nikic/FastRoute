<?php

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher;

class GroupCountBased implements Dispatcher {
    private $staticRoutes;
    private $variableRoutes;
    private $regexes;

    public function __construct($data) {
        list($this->staticRoutes, $this->variableRoutes, $this->regexes) = $data;
    }

    public function dispatch($httpMethod, $uri) {
        if (isset($this->staticRoutes[$uri])) {
            return $this->dispatchStaticRoute($httpMethod, $uri);
        } else {
            return $this->dispatchVariableRoute($httpMethod, $uri);
        }
    }

    private function dispatchStaticRoute($httpMethod, $uri) {
        $routes = $this->staticRoutes[$uri];

        if (isset($routes[$httpMethod])) {
            return [self::FOUND, $routes[$httpMethod], []];
        } elseif ($httpMethod === 'HEAD' && isset($routes['GET'])) {
            return [self::FOUND, $routes['GET'], []];
        } else {
            return [self::METHOD_NOT_ALLOWED, array_keys($routes)];
        }
    }

    private function dispatchVariableRoute($httpMethod, $uri) {
        foreach ($this->regexes as $i => $regex) {
            if (!preg_match($regex, $uri, $matches)) {
                continue;
            }

            $routes = $this->variableRoutes[$i][count($matches)];
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
