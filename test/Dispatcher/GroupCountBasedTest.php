<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher;

class GroupCountBasedTest extends DispatcherTest
{
    protected function getDispatcherClass(): string
    {
        return Dispatcher\GroupCountBased::class;
    }

    protected function getDataGeneratorClass(): string
    {
        return GroupCountBased::class;
    }
}
