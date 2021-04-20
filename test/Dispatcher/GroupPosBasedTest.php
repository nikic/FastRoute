<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;

class GroupPosBasedTest extends DispatcherTest
{
    /**
     * @inheritDoc
     */
    protected function getDispatcherClass()
    {
        return Dispatcher\GroupPosBased::class;
    }

    /**
     * @inheritDoc
     */
    protected function getDataGeneratorClass()
    {
        return new DataGenerator\RegexBased(new DataGenerator\GroupPosProcessor());
    }
}
