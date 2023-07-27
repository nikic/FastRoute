<?php
declare(strict_types=1);

namespace FastRoute\Benchmark;

use FastRoute\Dispatcher;
use PhpBench\Attributes as Bench;

#[Bench\Iterations(5)]
#[Bench\Revs(250)]
#[Bench\Warmup(3)]
#[Bench\BeforeMethods(['registerDispatchers'])]
#[Bench\ParamProviders(['dispatchers'])]
final class RoutingWithManyRoutesBench
{
    /** @var array<string, Dispatcher> */
    private array $dispatchers;

    public function registerDispatchers(): void
    {
        $this->dispatchers = [
            'group_count' => DispatcherForBenchmark::manyRoutes(Dispatcher\GroupCountBased::class),
            'char_count' => DispatcherForBenchmark::manyRoutes(Dispatcher\CharCountBased::class),
            'group_pos' => DispatcherForBenchmark::manyRoutes(Dispatcher\GroupPosBased::class),
            'mark' => DispatcherForBenchmark::manyRoutes(Dispatcher\MarkBased::class),
        ];
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['static', 'firstRoute'])]
    public function staticFirstRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/abc0');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['dynamic', 'firstRoute'])]
    public function dynamicFirstRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/abcbar/0');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['static', 'lastRoute'])]
    public function staticLastRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/abc399');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['dynamic', 'lastRoute'])]
    public function dynamicLastRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/abcbar/399');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['static', 'invalidMethod'])]
    public function staticInvalidMethod(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('PUT', '/abc399');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['dynamic', 'invalidMethod'])]
    public function dynamicInvalidMethod(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('PUT', '/abcbar/399');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['unknownRoute'])]
    public function unknownRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/testing');
    }

    /** @return iterable<string, array<string, mixed>> */
    public function dispatchers(): iterable
    {
        foreach (['group_count', 'char_count', 'group_pos', 'mark'] as $dispatcher) {
            yield $dispatcher => ['dispatcher' => $dispatcher];
        }
    }
}
