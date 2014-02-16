<?php

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher;

class GroupPosBased implements Dispatcher {
    private $staticRoutes;
    private $variableRoutes;
    private $regexes;

    public function __construct($data) {
        list($this->staticRoutes, $this->variableRoutes, $this->regexes) = $data;
    }

    public function dispatch($httpMethod, $uri) {
        if (isset($this->staticRoutes[$uri][$httpMethod])) {
            return [self::FOUND, $this->staticRoutes[$uri][$httpMethod], []];
        }

        return $this->matchRoute($httpMethod, $uri);
    }

    private function matchRoute($httpMethod, $uri) {
        foreach ($this->regexes as $i => $regex) {
            if (!preg_match($regex, $uri, $matches)) {
                continue;
            }

            // find first non-empty match
            for ($j = 1; '' === $matches[$j]; ++$j);

            $routes = $this->variableRoutes[$i][$j];
            if (!isset($routes[$httpMethod])) {
                return [self::METHOD_NOT_ALLOWED, array_keys($routes)];
            }

            $route = $routes[$httpMethod];
            $vars = [];
            foreach ($route->variables as $var) {
                $vars[$var] = $matches[$j++];
            }
            return [self::FOUND, $route->handler, $vars];
        }

        return [self::NOT_FOUND];
    }
}
