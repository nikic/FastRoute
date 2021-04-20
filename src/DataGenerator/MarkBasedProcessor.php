<?php
declare(strict_types=1);

namespace FastRoute\DataGenerator;

use function implode;

class MarkBasedProcessor implements ChunkProcessorInterface
{
    public function getApproxChunkSize(): int
    {
        return 30;
    }

    /**
     * {@inheritDoc}
     */
    public function processChunk(array $regexToRoutesMap): array
    {
        $routeMap = [];
        $regexes = [];
        $markName = 'a';

        foreach ($regexToRoutesMap as $regex => $route) {
            $regexes[] = $regex . '(*MARK:' . $markName . ')';
            $routeMap[$markName] = [$route->handler(), $route->variables(), $route];

            ++$markName;
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';

        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}
