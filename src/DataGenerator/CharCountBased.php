<?php

namespace FastRoute\DataGenerator;

class CharCountBased extends RegexBasedAbstract {
    protected function getApproxChunkSize() {
        return 30;
    }

    protected function processChunk($regexToRoutesMap) {
        $routeMap = array();
        $regexes = array();

        $suffixLen = 0;
        $suffix = '';
        $count = count($regexToRoutesMap);
        foreach ($regexToRoutesMap as $regex => $route) {
            $suffixLen++;
            $suffix .= "\t";

            $regexes[] = '(?:' . $regex . '/(\t{' . $suffixLen . '})\t{' . ($count - $suffixLen) . '})';
            $routeMap[$suffix] = array($route->handler, $route->variables);
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return array('regex' => $regex, 'suffix' => '/' . $suffix, 'routeMap' => $routeMap);
    }
}
