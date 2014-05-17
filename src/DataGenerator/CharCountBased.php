<?php

namespace FastRoute\DataGenerator;

class CharCountBased extends RegexBasedAbstract {
    const APPROX_CHUNK_SIZE = 30;

    public function getData() {
        if (empty($this->regexToRoutesMap)) {
            return [$this->staticRoutes, []];
        }

        return [$this->staticRoutes, $this->generateVariableRouteData()];
    }

    private function generateVariableRouteData() {
        $chunkSize = $this->computeChunkSize(count($this->regexToRoutesMap));
        $chunks = array_chunk($this->regexToRoutesMap, $chunkSize, true);
        return array_map([$this, 'processChunk'], $chunks);
    }

    private function computeChunkSize($count) {
        $numParts = max(1, round($count / self::APPROX_CHUNK_SIZE));
        return ceil($count / $numParts);
    }

    private function processChunk($regexToRoutesMap) {
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

            $regexes[] = '(?:' . $regex . '(\t{' . $suffixLen . '})\t{' . ($count - $suffixLen) . '})';
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'suffix' => $suffix, 'routeMap' => $routeMap];
    }
}