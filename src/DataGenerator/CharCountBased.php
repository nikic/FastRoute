<?php

namespace FastRoute\DataGenerator;

class CharCountBased extends RegexBasedAbstract {
    protected function getApproxChunkSize() {
        return 30;
    }

    protected function processChunk($regexToRoutesMap) {
        $routeMap = [];
        $regexes = [];

        $suffixLen = 0;
        $suffix = '';
        $count = count($regexToRoutesMap);
        foreach ($regexToRoutesMap as $regex => $routes) {
            $suffixLen++;
            $suffix .= "\t";

            foreach ($routes as $route) {
                $routeMap[$suffix][$route->httpMethod] = [
                    $route->handler, $route->variables
                ];
            }

            $regexes[] = '(?:' . $regex . '/(\t{' . $suffixLen . '})\t{' . ($count - $suffixLen) . '})';
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'suffix' => '/' . $suffix, 'routeMap' => $routeMap];
    }
}