<?php

namespace FastRoute\DataGenerator;

class GroupPosBased extends RegexBasedAbstract {
    const APPROX_CHUNK_SIZE = 10;

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
        $offset = 1;
        foreach ($regexToRoutesMap as $regex => $routes) {
            foreach ($routes as $route) {
                $routeMap[$offset][$route->httpMethod]
                    = [$route->handler, $route->variables];
            }

            $regexes[] = $regex;
            $offset += count(reset($routes)->variables);
        }

        $regex = '~^(?:' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}

