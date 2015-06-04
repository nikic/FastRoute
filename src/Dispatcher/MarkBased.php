<?php

namespace FastRoute\Dispatcher;

use FastRoute\DispatcherResult\FoundResult;
use FastRoute\DispatcherResult\NotFoundResult;

class MarkBased extends RegexBasedAbstract {
    public function __construct($data) {
        list($this->staticRouteMap, $this->variableRouteData) = $data;
    }

    protected function dispatchVariableRoute($routeData, $uri) {
        foreach ($routeData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            list($handler, $varNames) = $data['routeMap'][$matches['MARK']];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }
            return new FoundResult($handler, $vars);
        }

        return new NotFoundResult();
    }
}
