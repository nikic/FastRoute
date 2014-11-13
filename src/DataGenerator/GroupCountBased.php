<?php

namespace FastRoute\DataGenerator;

class GroupCountBased extends RegexBasedAbstract {
    protected function getApproxChunkSize() {
        return 10;
    }

    protected function processChunk($regexToRoutesMap) {
        $routeMap = array();
        $regexes = array();
        $numGroups = 0;
        foreach ($regexToRoutesMap as $regex => $routes) {
            $numVariables = count(reset($routes)->variables);
            $numGroups = max($numGroups, $numVariables);

            $regexes[] = $regex . str_repeat('()', $numGroups - $numVariables);

            foreach ($routes as $route) {
                $routeMap[$numGroups + 1][$route->httpMethod]
                    = array($route->handler, $route->variables);
            }

            ++$numGroups;
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return array('regex' => $regex, 'routeMap' => $routeMap);
    }
}

