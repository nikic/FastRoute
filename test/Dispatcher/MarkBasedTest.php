<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;

class MarkBasedTest extends DispatcherTest
{
    /**
     * @inheritDoc
     */
    protected function getDispatcherClass()
    {
        return Dispatcher\MarkBased::class;
    }

    /**
     * @inheritDoc
     */
    protected function getDataGeneratorClass()
    {
        return new DataGenerator\RegexBased(new DataGenerator\MarkBasedProcessor());
    }
}
