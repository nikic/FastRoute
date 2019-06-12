<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;

class MarkBasedTest extends DispatcherTest
{
    protected function getDispatcherClass(): string
    {
        return Dispatcher\MarkBased::class;
    }

    protected function getDataGeneratorClass(): string
    {
        return DataGenerator\MarkBased::class;
    }
}
