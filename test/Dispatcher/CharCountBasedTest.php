<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

class CharCountBasedTest extends DispatcherTest
{
    protected function getDispatcherClass()
    {
        return 'FastRoute\\Dispatcher\\CharCountBased';
    }

    protected function getDataGeneratorClass()
    {
        return 'FastRoute\\DataGenerator\\CharCountBased';
    }
}
