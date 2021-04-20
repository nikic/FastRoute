<?php
declare(strict_types=1);

namespace FastRoute\DataGenerator;

/**
 * @deprecated Pass the chunk processor to the constructor of your data generator instead
 */
class CharCountBased extends RegexBasedAbstract
{
    protected function getChunkProcessor(): ChunkProcessorInterface
    {
        if ($this->chunkProcessor === null) {
            $this->chunkProcessor = new CharCountProcessor();
        }

        return $this->chunkProcessor;
    }
}
