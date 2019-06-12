<?php
declare(strict_types=1);

namespace FastRoute\DataGenerator;

use function implode;

class MarkBased extends RegexBasedAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function getApproxChunkSize(): int
    {
        return 30;
    }

    /**
     * {@inheritDoc}
     */
    protected function processChunk(array $regexToRoutesMap): array
    {
        $routeMap = [];
        $regexes = [];
        $markName = 'a';

        foreach ($regexToRoutesMap as $regex => $route) {
            $regexes[] = $regex . '(*MARK:' . $markName . ')';
            $routeMap[$markName] = [$route->handler, $route->variables];

            ++$markName;
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';

        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}
