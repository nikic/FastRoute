<?php

namespace FastRoute\Dispatcher;

class MarkBasedTest extends DispatcherTest {
    public function setUp() {
        if (version_compare(PHP_VERSION, '5.6.0-beta1', '<')) {
            $this->markTestSkipped('PHP 5.6 required for MARK support');
        }
    }

    protected function getDispatcherClass() {
        return 'FastRoute\\Dispatcher\\MarkBased';
    }

    protected function getDataGeneratorClass() {
        return 'FastRoute\\DataGenerator\\MarkBased';
    }
}
