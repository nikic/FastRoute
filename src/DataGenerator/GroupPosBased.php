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
        foreach ($regexToRoutesMap as $regex => $route) {
            $regexes[] = $regex;
            $routeMap[$offset] = array($route->handler, $route->variables);

            $offset += count($route->variables);
        }

        $regex = '~^(?:' . implode('|', $regexes) . ')$~';
        return array('regex' => $regex, 'routeMap' => $routeMap);
    }
}

