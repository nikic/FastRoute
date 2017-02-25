<?php

namespace FastRoute\Dispatcher;

use FastRoute\Exception\HttpNotFoundException;

class MarkBased extends RegexBasedAbstract {
    public function __construct($data) {
        list($this->staticRouteMap, $this->variableRouteData) = $data;
    }

    protected function dispatchVariableRoute($routeData, $uri) {
        foreach ($routeData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            $route = $data['routeMap'][$matches['MARK']];

            $vars = [];
            $i = 0;
            foreach ($route->variables as $varName) {
                $vars[$varName] = $matches[++$i];
            }
            $route->variables = $vars;
            return $route;
        }

        throw new HttpNotFoundException;
    }
}
