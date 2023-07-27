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
final class RoutingWithRealLifeExampleBench
{
    /** @var array<string, Dispatcher> */
    private array $dispatchers;

    public function registerDispatchers(): void
    {
        $this->dispatchers = [
            'group_count' => DispatcherForBenchmark::realLifeExample(Dispatcher\GroupCountBased::class),
            'char_count' => DispatcherForBenchmark::realLifeExample(Dispatcher\CharCountBased::class),
            'group_pos' => DispatcherForBenchmark::realLifeExample(Dispatcher\GroupPosBased::class),
            'mark' => DispatcherForBenchmark::realLifeExample(Dispatcher\MarkBased::class),
        ];
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['static', 'firstRoute'])]
    public function staticFirstRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['dynamic', 'firstRoute'])]
    public function dynamicFirstRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/page/hello-word');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['static', 'lastRoute'])]
    public function staticLastRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/admin/category');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['dynamic', 'lastRoute'])]
    public function dynamicLastRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/admin/category/123');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['static', 'invalidMethod'])]
    public function staticInvalidMethod(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('PUT', '/about-us');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['dynamic', 'invalidMethod'])]
    public function dynamicInvalidMethod(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('PATCH', '/shop/category/123');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['unknownRoute'])]
    public function unknownRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/shop/product/awesome');
    }

    /** @param array{dispatcher: string} $params */
    #[Bench\Subject]
    #[Bench\Groups(['longestRoute'])]
    public function longestRoute(array $params): void
    {
        $this->dispatchers[$params['dispatcher']]->dispatch('GET', '/shop/category/123/product/search/status:sale');
    }

    /** @return iterable<string, array<string, mixed>> */
    public function dispatchers(): iterable
    {
        foreach (['group_count', 'char_count', 'group_pos', 'mark'] as $dispatcher) {
            yield $dispatcher => ['dispatcher' => $dispatcher];
        }
    }
}
