<?php

namespace FastRoute\DataGenerator;

class GroupPosBased extends RegexBasedAbstract {
    protected function getApproxChunkSize() {
        return 10;
    }

    protected function processChunk($regexToRoutesMap) {
        $routeMap = array();
        $regexes = array();
        $offset = 1;
        foreach ($regexToRoutesMap as $regex => $routes) {
            foreach ($routes as $route) {
                $routeMap[$offset][$route->httpMethod]
                    = array($route->handler, $route->variables);
            }

            $regexes[] = $regex;
            $offset += count(reset($routes)->variables);
        }

        $regex = '~^(?:' . implode('|', $regexes) . ')$~';
        return array('regex' => $regex, 'routeMap' => $routeMap);
    }
}

