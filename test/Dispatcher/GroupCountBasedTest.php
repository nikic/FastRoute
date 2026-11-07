<?php

namespace FastRoute\Dispatcher;

class GroupCountBasedTest extends DispatcherTest
{
    protected function getDispatcherClass()
    {
        return \FastRoute\Dispatcher\GroupCountBased::class;
    }

    protected function getDataGeneratorClass()
    {
        return \FastRoute\DataGenerator\GroupCountBased::class;
    }
}
