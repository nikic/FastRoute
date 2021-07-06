<?php
declare(strict_types=1);

namespace FastRoute\Test\Dispatcher;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;

final class CompactMarkBasedTest extends DispatcherTest
{
    protected function getDispatcherClass(): string
    {
        return Dispatcher\CompactMarkBased::class;
    }

    protected function getDataGeneratorClass(): string
    {
        return DataGenerator\CompactMarkBased::class;
    }
}
