<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use function preg_match;

class GroupPosBased extends RegexBasedAbstract
{
    /** @inheritDoc */
    protected function dispatchVariableRoute(array $routeData, string $uri): ?Result
    {
        foreach ($routeData as $data) {
            if (! preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            // find first non-empty match
            // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedFor
            for ($i = 1; $matches[$i] === ''; ++$i) {
            }

            $route = $data['routeMap'][$i];

            $vars = [];
            foreach ($route->variables as $varName) {
                $vars[$varName] = $matches[$i++];
            }

            return Result::found($route->handler, $vars);
        }

        return null;
    }
}
