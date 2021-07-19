<?php
declare(strict_types=1);

namespace FastRoute\Benchmark;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use Generator;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Subject;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;

use function array_keys;
use function assert;

/**
 * @ParamProviders({"provideDispatcher"})
 *
 * @Warmup(50)
 * @Revs(500)
 * @Iterations(5)
 */
abstract class Benchmark
{
    private const DISPATCHERS_CONFIG = [
        'group_count' => [
            'dataGenerator' => DataGenerator\GroupCountBased::class,
            'dispatcher' => Dispatcher\GroupCountBased::class,
        ],
        'char_count' => [
            'dataGenerator' => DataGenerator\CharCountBased::class,
            'dispatcher' => Dispatcher\CharCountBased::class,
        ],
        'group_pos' => [
            'dataGenerator' => DataGenerator\GroupPosBased::class,
            'dispatcher' => Dispatcher\GroupPosBased::class,
        ],
        'mark' => [
            'dataGenerator' => DataGenerator\MarkBased::class,
            'dispatcher' => Dispatcher\MarkBased::class,
        ],
    ];

    /** @var Dispatcher[] */
    private array $dispatchers = [];

    final public function initializeDispatchers(): void
    {
        foreach (self::DISPATCHERS_CONFIG as $name => $config) {
            $this->dispatchers[$name] = $this->createDispatcher($config);
        }
    }

    /** @param array{dataGenerator: string, dispatcher: string} $options */
    abstract protected function createDispatcher(array $options): Dispatcher;

    /** @return Generator<string, array<string, mixed>> */
    abstract public function provideStaticRoutes(): iterable;

    /** @return Generator<string, array<string, mixed>> */
    abstract public function provideDynamicRoutes(): iterable;

    /** @return Generator<string, array<string, mixed>> */
    abstract public function provideOtherScenarios(): iterable;

    /** @return Generator<string, array<string, string>> */
    final public function provideDispatcher(): iterable
    {
        foreach (array_keys(self::DISPATCHERS_CONFIG) as $dispatcher) {
            yield $dispatcher => ['dispatcher' => $dispatcher];
        }
    }

    /**
     * @ParamProviders({"provideStaticRoutes"}, extend=true)
     *
     * @param array<string, string|array{0: int, 1: string[]|mixed, 2?: array<string, string>}> $params
     *
     * @Subject
     * @BeforeMethods({"initializeDispatchers"})
     */
    final public function staticRoutes(array $params): void
    {
        $this->runScenario($params);
    }

    /**
     * @ParamProviders({"provideDynamicRoutes"}, extend=true)
     *
     * @param array<string, string|array{0: int, 1: string[]|mixed, 2?: array<string, string>}> $params
     *
     * @Subject
     * @BeforeMethods({"initializeDispatchers"})
     */
    final public function dynamicRoutes(array $params): void
    {
        $this->runScenario($params);
    }

    /**
     * @ParamProviders({"provideOtherScenarios"}, extend=true)
     *
     * @param array<string, string|array{0: int, 1: string[]|mixed, 2?: array<string, string>}> $params
     *
     * @Subject
     * @BeforeMethods({"initializeDispatchers"})
     */
    final public function otherRoutes(array $params): void
    {
        $this->runScenario($params);
    }

    /** @param array<string, string|array{0: int, 1: string[]|mixed, 2?: array<string, string>}> $params */
    private function runScenario(array $params): void
    {
        $dispatcher = $this->dispatchers[$params['dispatcher']];

        assert($params['result'] === $dispatcher->dispatch($params['method'], $params['route']));
    }

    /**
     * @param array{dispatcher: string} $params
     *
     * @Subject
     */
    final public function routeRegistration(array $params): void
    {
        $this->createDispatcher(self::DISPATCHERS_CONFIG[$params['dispatcher']]);
    }
}
