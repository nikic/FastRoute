<?php

namespace FastRoute\Dispatcher;

class CharCountBased extends RegexBasedAbstract
{
    protected function dispatchVariableRoute(array $routeData, string $uri):array
    {
        foreach ($routeData as $data) {
            if (!preg_match($data['regex'], $uri . $data['suffix'], $matches)) {
                continue;
            }

            [$handler, $varNames] = $data['routeMap'][end($matches)];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }
            return [self::FOUND, $handler, $vars];
        }

        return [self::NOT_FOUND];
    }
}
