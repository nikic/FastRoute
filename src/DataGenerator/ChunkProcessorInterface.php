<?php
declare(strict_types=1);

namespace FastRoute\DataGenerator;

use FastRoute\RouteInterface;

interface ChunkProcessorInterface
{
    /**
     * @param array<string, RouteInterface> $regexToRoutesMap
     *
     * @return mixed[]
     */
    public function processChunk(array $regexToRoutesMap): array;

    public function getApproxChunkSize(): int;
}
