<?php
declare(strict_types=1);

namespace FastRoute\DataGenerator;

use function count;
use function implode;
use function max;
use function str_repeat;

class GroupCountBased extends RegexBasedAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function getApproxChunkSize(): int
    {
        return 10;
    }

    /**
     * {@inheritDoc}
     */
    protected function processChunk(array $regexToRoutesMap): array
    {
        $routeMap = [];
        $regexes = [];
        $numGroups = 0;
        foreach ($regexToRoutesMap as $regex => $route) {
            $numVariables = count($route->variables);
            $numGroups = max($numGroups, $numVariables);

            $regexes[] = $regex . str_repeat('()', $numGroups - $numVariables);
            $routeMap[$numGroups + 1] = [$route->handler, $route->variables];

            ++$numGroups;
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';

        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}
