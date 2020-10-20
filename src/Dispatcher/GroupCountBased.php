<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use FastRoute\Result;
use function count;
use function preg_match;

class GroupCountBased extends RegexBasedAbstract
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

            [$handler, $varNames] = $data['routeMap'][count($matches)];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }

            return Result::fromArray([self::FOUND, $handler, $vars]);
        }

        return Result::fromArray([self::NOT_FOUND]);
    }
}
