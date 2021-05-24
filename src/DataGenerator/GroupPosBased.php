<?php
declare(strict_types=1);

namespace FastRoute\DataGenerator;

use function count;
use function implode;

class GroupPosBased extends RegexBasedAbstract
{
    protected function getApproxChunkSize(): int
    {
        return 10;
    }

    /** @inheritDoc */
    protected function processChunk(array $regexToRoutesMap): array
    {
        $routeMap = [];
        $regexes = [];
        $offset = 1;
        foreach ($regexToRoutesMap as $regex => $route) {
            $regexes[] = $regex;
            $routeMap[$offset] = [$route->handler, $route->variables];

            $offset += count($route->variables);
        }

        $regex = '~^(?:' . implode('|', $regexes) . ')$~';

        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}
