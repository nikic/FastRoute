<?php

namespace FastRoute\DataGenerator;

class MarkBased extends RegexBasedAbstract {
    protected function getApproxChunkSize() {
        return 30;
    }

    protected function processChunk($regexToRoutesMap) {
        $routeMap = [];
        $regexes = [];
        $markName = 'a';
        foreach ($regexToRoutesMap as $regex => $routes) {
            $regexes[] = $regex . '(*MARK:' . $markName . ')';

            foreach ($routes as $route) {
                $routeMap[$markName][$route->httpMethod]
                    = [$route->handler, $route->variables];
            }

            ++$markName;
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}

