<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use function preg_match;

class GroupPosBased extends RegexBasedAbstract
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

            // find first non-empty match
            /** @noinspection PhpStatementHasEmptyBodyInspection */
            for ($i = 1; $matches[$i] === ''; ++$i) {
            }

            [$handler, $varNames, $route] = $data['routeMap'][$i];

            $vars = [];
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[$i++];
            }

            return Result::fromArray([self::FOUND, $handler, $vars, $route]);
        }

        return Result::createNotFound();
    }
}
