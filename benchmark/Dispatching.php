<?php
declare(strict_types=1);

namespace FastRoute\Benchmark;

use FastRoute\Cache;
use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use Generator;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;

use function assert;

/**
 * @Warmup(2)
 * @Revs(1000)
 * @Iterations(5)
 * @BeforeMethods({"initializeDispatchers"})
 */
abstract class Dispatching
{
    /** @var Dispatcher[] */
    private array $dispatchers = [];

    public function initializeDispatchers(): void
    {
        $this->dispatchers['group_count'] = $this->createDispatcher(
            [
                'dataGenerator' => DataGenerator\GroupCountBased::class,
                'dispatcher' => Dispatcher\GroupCountBased::class,
            ]
        );
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

    /** @param array{routeParser?: string, dataGenerator?: string, dispatcher?: string, routeCollector?: string, cacheDisabled?: bool, cacheKey?: string, cacheDriver?: string|Cache} $options */
    abstract protected function createDispatcher(array $options = []): Dispatcher;

    /** @return Generator<string, array<string, mixed>> */
    abstract public function provideStaticRoutes(): iterable;

    /** @return Generator<string, array<string, mixed>> */
    abstract public function provideDynamicRoutes(): iterable;

    /** @return Generator<string, array<string, mixed>> */
    abstract public function provideOtherScenarios(): iterable;

    /** @return Generator<string, array<string, string>> */
    public function provideDispatcher(): iterable
    {
        yield 'group_count' => ['dispatcher' => 'group_count'];
        yield 'char-count' => ['dispatcher' => 'char_count'];
        yield 'group-pos' => ['dispatcher' => 'group_pos'];
        yield 'mark' => ['dispatcher' => 'mark'];
    }

    /**
     * @ParamProviders({"provideDispatcher", "provideStaticRoutes"})
     *
     * @param array<string, string|array{0: int, 1: string[]|mixed, 2?: array<string, string>}> $params
     */
    public function benchStaticRoutes(array $params): void
    {
        $this->runScenario($params);
    }

    /**
     * @ParamProviders({"provideDispatcher", "provideDynamicRoutes"})
     *
     * @param array<string, string|array{0: int, 1: string[]|mixed, 2?: array<string, string>}> $params
     */
    public function benchDynamicRoutes(array $params): void
    {
        $this->runScenario($params);
    }

    /**
     * @ParamProviders({"provideDispatcher", "provideOtherScenarios"})
     *
     * @param array<string, string|array{0: int, 1: string[]|mixed, 2?: array<string, string>}> $params
     */
    public function benchOtherRoutes(array $params): void
    {
        $this->runScenario($params);
    }

    /** @param array<string, string|array{0: int, 1: string[]|mixed, 2?: array<string, string>}> $params */
    private function runScenario(array $params): void
    {
        $dispatcher = $this->dispatchers[$params['dispatcher']];

        assert($params['result'] === $dispatcher->dispatch($params['method'], $params['route']));
    }
}
