<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

/**
 * Result Factory
 */
interface ResultFactoryInterface
{
    public function createResult(): ResultInterface;

    /**
     * @param mixed[] $result
     */
    public function createResultFromArray(array $result): ResultInterface;
}
