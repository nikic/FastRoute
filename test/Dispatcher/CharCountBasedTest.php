<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;

class CharCountBasedTest extends DispatcherTest
{
    /**
     * @inheritDoc
     */
    protected function getDispatcherClass()
    {
        return Dispatcher\CharCountBased::class;
    }

    /**
     * @inheritDoc
     */
    protected function getDataGeneratorClass()
    {
        return new DataGenerator\RegexBased(new DataGenerator\CharCountProcessor());
    }
}
