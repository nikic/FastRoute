<?php

namespace FastRoute\Dispatcher;

class CharCountBasedTest extends DispatcherTest
{
    protected function getDispatcherClass()
    {
        return \FastRoute\Dispatcher\CharCountBased::class;
    }

    protected function getDataGeneratorClass()
    {
        return \FastRoute\DataGenerator\CharCountBased::class;
    }
}
