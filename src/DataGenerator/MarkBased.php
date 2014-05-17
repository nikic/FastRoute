<?php

namespace FastRoute\DataGenerator;

class MarkBased extends RegexBasedAbstract {
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

