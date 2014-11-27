<?php

namespace FastRoute\Dispatcher;

class CharCountBased extends RegexBasedAbstract {
    public function __construct($data) {
        list($this->staticRouteMap, $this->variableRouteData) = $data;
    }

    protected function dispatchVariableRoute($routeData, $uri) {
        foreach ($routeData as $data) {
            if (!preg_match($data['regex'], $uri . $data['suffix'], $matches)) {
                continue;
            }

            list($handler, $varNames) = $data['routeMap'][end($matches)];

            $vars = array();
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }
            return array(self::FOUND, $handler, $vars);
        }

        return array(self::NOT_FOUND);
    }
}
