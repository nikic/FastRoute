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
        $index = 0;
        $tabs = '';

        foreach ($regexToRoutesMap as $regex => $routes) {
            $index++;
            $tabs .= "\t";

            foreach ($routes as $route) {
                $routeMap[$tabs][$route->httpMethod] = [
                    $route->handler, $route->variables
                ];
            }

            $regexes[] = '(?:(' . $regex . ')(?<id>[\t]{' . $index . '}))';
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')~';

        return ['regex' => $regex, 'tabs' => $tabs, 'routeMap' => $routeMap];
    }
}