<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\DataGenerator\GroupCountProcessor;
use FastRoute\DataGenerator\RegexBased;
use FastRoute\Dispatcher;

class GroupCountBasedTest extends DispatcherTest
{
    /**
     * @inheritDoc
     */
    protected function getDispatcherClass()
    {
        return Dispatcher\GroupCountBased::class;
    }

    /**
     * @inheritDoc
     */
    protected function getDataGeneratorClass()
    {
        return new RegexBased(new GroupCountProcessor());
    }
}
