<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use function preg_match;

class MarkBased extends RegexBasedAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function dispatchVariableRoute(array $routeData, string $uri): Result
    {
        foreach ($routeData as $data) {
            if (! preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            [$handler, $varNames, $route] = $data['routeMap'][$matches['MARK']];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }

            return Result::fromArray([self::FOUND, $handler, $vars, $route]);
        }

        return Result::createNotFound();
    }
}
