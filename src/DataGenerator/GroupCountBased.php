<?php
declare(strict_types=1);

namespace FastRoute\DataGenerator;

/**
 * @deprecated Pass the chunk processor to the constructor of your data generator instead
 */
class GroupCountBased extends RegexBasedAbstract
{
    protected function getChunkProcessor(): ChunkProcessorInterface
    {
        if ($this->chunkProcessor === null) {
            $this->chunkProcessor = new GroupCountProcessor();
        }

        return $this->chunkProcessor;
    }
}
