<?php
declare(strict_types=1);

namespace FastRoute\Benchmark;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;
use PHPUnit\Framework\Assert;

/**
 * @Warmup(2)
 * @Revs(1000)
 * @Iterations(5)
 * @BeforeMethods({"initializeDispatchers"})
 */
abstract class Dispatching
{
    /**
     * @var Dispatcher[]
     */
    private $dispatchers = [];

    public function initializeDispatchers(): void
    {
        $this->dispatchers['default'] = $this->createDispatcher();
        $this->dispatchers['char_count'] = $this->createDispatcher(
            [
                'dataGenerator' => DataGenerator\CharCountBased::class,
                'dispatcher' => Dispatcher\CharCountBased::class,
            ]
        );
        $this->dispatchers['group_pos'] = $this->createDispatcher(
            [
                'dataGenerator' => DataGenerator\GroupPosBased::class,
                'dispatcher' => Dispatcher\GroupPosBased::class,
            ]
        );
        $this->dispatchers['mark'] = $this->createDispatcher(
            [
                'dataGenerator' => DataGenerator\MarkBased::class,
                'dispatcher' => Dispatcher\MarkBased::class,
            ]
        );
    }

    abstract protected function createDispatcher(array $options = []): Dispatcher;

    abstract public function provideStaticRoutes(): iterable;

    abstract public function provideDynamicRoutes(): iterable;

    abstract public function provideOtherScenarios(): iterable;

    public function provideDispatcher(): iterable
    {
        yield 'default' => ['dispatcher' => 'default'];
        yield 'char-count' => ['dispatcher' => 'char_count'];
        yield 'group-pos' => ['dispatcher' => 'group_pos'];
        yield 'mark' => ['dispatcher' => 'mark'];
    }

    /**
     * @ParamProviders({"provideDispatcher", "provideStaticRoutes"})
     */
    public function benchStaticRoutes(array $params): void
    {
        $this->runScenario($params);
    }

    /**
     * @ParamProviders({"provideDispatcher", "provideDynamicRoutes"})
     */
    public function benchDynamicRoutes(array $params): void
    {
        $this->runScenario($params);
    }

    /**
     * @ParamProviders({"provideDispatcher", "provideOtherScenarios"})
     */
    public function benchOtherRoutes(array $params): void
    {
        $this->runScenario($params);
    }

    private function runScenario(array $params): void
    {
        $dispatcher = $this->dispatchers[$params['dispatcher']];
        $result = $dispatcher->dispatch($params['method'], $params['route']);

        Assert::assertSame($params['result'], $result);
    }
}
