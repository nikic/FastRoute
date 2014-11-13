<?php

namespace FastRoute\DataGenerator;

class MarkBased extends RegexBasedAbstract {
    protected function getApproxChunkSize() {
        return 30;
    }

    protected function processChunk($regexToRoutesMap) {
        $routeMap = array();
        $regexes = array();
        $markName = 'a';
        foreach ($regexToRoutesMap as $regex => $routes) {
            $regexes[] = $regex . '(*MARK:' . $markName . ')';

            foreach ($routes as $route) {
                $routeMap[$markName][$route->httpMethod]
                    = array($route->handler, $route->variables);
            }

            ++$markName;
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return array('regex' => $regex, 'routeMap' => $routeMap);
    }
}

