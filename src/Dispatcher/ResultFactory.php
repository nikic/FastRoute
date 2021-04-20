<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

/**
 * Result Factory
 */
class ResultFactory implements ResultFactoryInterface
{
    public function createResult(): ResultInterface
    {
        return new Result();
    }

    /**
     * @inheritDoc
     */
    public function createResultFromArray(array $result): ResultInterface
    {
        return Result::fromArray($result);
    }
}
