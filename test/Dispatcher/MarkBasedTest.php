<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

class MarkBasedTest extends DispatcherTest
{
    protected function getDispatcherClass(): string
    {
        return 'FastRoute\\Dispatcher\\MarkBased';
    }

    protected function getDataGeneratorClass(): string
    {
        return 'FastRoute\\DataGenerator\\MarkBased';
    }
}
