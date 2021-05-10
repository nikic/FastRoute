<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use function count;
use function preg_match;

class GroupCountBased extends RegexBasedAbstract
{
    /** @inheritDoc */
    protected function dispatchVariableRoute(array $routeData, string $uri): ?Result
    {
        foreach ($routeData as $data) {
            if (! preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            $route = $data['routeMap'][count($matches)];

            $vars = [];
            $i = 0;
            foreach ($route->variables as $varName) {
                $vars[$varName] = $matches[++$i];
            }

            return Result::found($route->handler, $vars);
        }

        return null;
    }
}
