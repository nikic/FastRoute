<?php

namespace FastRoute\Dispatcher;

class GroupPosBasedTest extends DispatcherTest
{
    protected function getDispatcherClass()
    {
        return \FastRoute\Dispatcher\GroupPosBased::class;
    }

    protected function getDataGeneratorClass()
    {
        return \FastRoute\DataGenerator\GroupPosBased::class;
    }
}
