<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;

class GroupPosBasedTest extends DispatcherTest
{
    protected function getDispatcherClass(): string
    {
        return Dispatcher\GroupPosBased::class;
    }

    protected function getDataGeneratorClass(): string
    {
        return DataGenerator\GroupPosBased::class;
    }
}
