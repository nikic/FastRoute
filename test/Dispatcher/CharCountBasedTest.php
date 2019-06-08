<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;

class CharCountBasedTest extends DispatcherTest
{
    protected function getDispatcherClass(): string
    {
        return Dispatcher\CharCountBased::class;
    }

    protected function getDataGeneratorClass(): string
    {
        return DataGenerator\CharCountBased::class;
    }
}
