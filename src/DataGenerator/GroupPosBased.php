<?php

namespace FastRoute\DataGenerator;

class GroupPosBased extends RegexBasedAbstract {
    const APPROX_CHUNK_SIZE = 10;

    public function getData() {
        if (empty($this->regexToRoutesMap)) {
            return [$this->staticRoutes, [], []];
        }

        list($variableRoutes, $regexes) = $this->generateVariableRouteData();
        return [$this->staticRoutes, $variableRoutes, $regexes];
    }

    private function generateVariableRouteData() {
        $variableRoutes = [];
        $regexes = [];

        $chunkSize = $this->computeChunkSize(count($this->regexToRoutesMap));
        $chunks = array_chunk($this->regexToRoutesMap, $chunkSize, true);
        foreach ($chunks as $regexToRoutesMap) {
            list($curVariableRoutes, $curRegex) = $this->processChunk($regexToRoutesMap);

            $variableRoutes[] = $curVariableRoutes;
            $regexes[] = $curRegex;
        }

        return [$variableRoutes, $regexes];
    }

    private function computeChunkSize($count) {
        $numParts = max(1, round($count / self::APPROX_CHUNK_SIZE));
        return ceil($count / $numParts);
    }

    private function processChunk($regexToRoutesMap) {
        $variableRoutes = [];
        $regexes = [];
        $offset = 1;
        foreach ($regexToRoutesMap as $regex => $routes) {
            foreach ($routes as $route) {
                $variableRoutes[$offset][$route->httpMethod]
                    = [$route->handler, $route->variables];
            }

            $regexes[] = $regex;
            $offset += count(reset($routes)->variables);
        }

        $regex = '~^(?:' . implode('|', $regexes) . ')$~';
        return [$variableRoutes, $regex];
    }
}

