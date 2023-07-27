<?php
declare(strict_types=1);

namespace FastRoute\Benchmark;

use FastRoute\Dispatcher;
use PhpBench\Attributes as Bench;

#[Bench\Iterations(5)]
#[Bench\Revs(100)]
#[Bench\Warmup(3)]
final class RouteRegistrationBench
{
    #[Bench\Subject]
    #[Bench\Groups(['group_count'])]
    public function groupCount(): void
    {
        DispatcherForBenchmark::realLifeExample(Dispatcher\GroupCountBased::class);
    }

    #[Bench\Subject]
    #[Bench\Groups(['char_count'])]
    public function charCount(): void
    {
        DispatcherForBenchmark::realLifeExample(Dispatcher\CharCountBased::class);
    }

    #[Bench\Subject]
    #[Bench\Groups(['group_pos'])]
    public function groupPos(): void
    {
        DispatcherForBenchmark::realLifeExample(Dispatcher\GroupPosBased::class);
    }

    #[Bench\Subject]
    #[Bench\Groups(['mark'])]
    public function mark(): void
    {
        DispatcherForBenchmark::realLifeExample(Dispatcher\MarkBased::class);
    }
}
