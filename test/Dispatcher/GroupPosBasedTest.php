<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

class GroupPosBasedTest extends DispatcherTest
{
    protected function getDispatcherClass(): string
    {
        return 'FastRoute\\Dispatcher\\GroupPosBased';
    }

    protected function getDataGeneratorClass(): string
    {
        return 'FastRoute\\DataGenerator\\GroupPosBased';
    }
}
