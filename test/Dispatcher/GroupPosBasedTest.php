<?php

namespace FastRoute\Test\Dispatcher;

class GroupPosBasedTest extends DispatcherTest
{
    protected function getDispatcherClass()
    {
        return 'FastRoute\\Dispatcher\\GroupPosBased';
    }

    protected function getDataGeneratorClass()
    {
        return 'FastRoute\\DataGenerator\\GroupPosBased';
    }
}
