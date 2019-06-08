<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

class MarkBasedTest extends DispatcherTest
{
    protected function getDispatcherClass()
    {
        return 'FastRoute\\Dispatcher\\MarkBased';
    }

    protected function getDataGeneratorClass()
    {
        return 'FastRoute\\DataGenerator\\MarkBased';
    }
}
