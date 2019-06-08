<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

class GroupCountBasedTest extends DispatcherTest
{
    protected function getDispatcherClass(): string
    {
        return 'FastRoute\\Dispatcher\\GroupCountBased';
    }

    protected function getDataGeneratorClass(): string
    {
        return 'FastRoute\\DataGenerator\\GroupCountBased';
    }
}
