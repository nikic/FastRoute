<?php
declare(strict_types=1);

namespace FastRoute;

use Closure;
use FastRoute\Cache\FileCache;

use function assert;
use function is_string;

/** @phpstan-import-type ProcessedData from ConfigureRoutes */
final class FastRoute
{
    /** @var ProcessedData|null */
    private ?array $processedConfiguration = null;

    /**
     * @param Closure(ConfigureRoutes):void  $routeDefinitionCallback
     * @param class-string<RouteParser>      $routeParser
     * @param class-string<DataGenerator>    $dataGenerator
     * @param class-string<Dispatcher>       $dispatcher
     * @param class-string<ConfigureRoutes>  $routesConfiguration
     * @param Cache|class-string<Cache>|null $cacheDriver
     */
    private function __construct(
        private readonly Closure $routeDefinitionCallback,
        private readonly string $routeParser,
        private readonly string $dataGenerator,
        private readonly string $dispatcher,
        private readonly string $routesConfiguration,
        private readonly Cache|string|null $cacheDriver,
        private readonly ?string $cacheKey,
    ) {
    }

    /** @param Closure(ConfigureRoutes):void $routeDefinitionCallback */
    public static function recommendedSettings(Closure $routeDefinitionCallback, string $cacheKey): self
    {
        return new self(
            $routeDefinitionCallback,
            RouteParser\Std::class,
            DataGenerator\MarkBased::class,
            Dispatcher\MarkBased::class,
            RouteCollector::class,
            FileCache::class,
            $cacheKey,
        );
    }

    public function disableCache(): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->routeParser,
            $this->dataGenerator,
            $this->dispatcher,
            $this->routesConfiguration,
            null,
            null,
        );
    }

    /** @param Cache|class-string<Cache> $driver */
    public function withCache(Cache|string $driver, string $cacheKey): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->routeParser,
            $this->dataGenerator,
            $this->dispatcher,
            $this->routesConfiguration,
            $driver,
            $cacheKey,
        );
    }

    public function useCharCountDispatcher(): self
    {
        return $this->useCustomDispatcher(DataGenerator\CharCountBased::class, Dispatcher\CharCountBased::class);
    }

    public function useGroupCountDispatcher(): self
    {
        return $this->useCustomDispatcher(DataGenerator\GroupCountBased::class, Dispatcher\GroupCountBased::class);
    }

    public function useGroupPosDispatcher(): self
    {
        return $this->useCustomDispatcher(DataGenerator\GroupPosBased::class, Dispatcher\GroupPosBased::class);
    }

    public function useMarkDispatcher(): self
    {
        return $this->useCustomDispatcher(DataGenerator\MarkBased::class, Dispatcher\MarkBased::class);
    }

    /**
     * @param class-string<DataGenerator> $dataGenerator
     * @param class-string<Dispatcher>    $dispatcher
     */
    public function useCustomDispatcher(string $dataGenerator, string $dispatcher): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->routeParser,
            $dataGenerator,
            $dispatcher,
            $this->routesConfiguration,
            $this->cacheDriver,
            $this->cacheKey,
        );
    }

    /** @return ProcessedData */
    private function buildConfiguration(): array
    {
        $loader = function (): array {
            $configuredRoutes = new $this->routesConfiguration(
                new $this->routeParser(),
                new $this->dataGenerator(),
            );

            ($this->routeDefinitionCallback)($configuredRoutes);

            return $configuredRoutes->processedRoutes();
        };

        if ($this->cacheDriver === null) {
            return $loader();
        }

        assert(is_string($this->cacheKey));

        $cache = is_string($this->cacheDriver)
            ? new $this->cacheDriver()
            : $this->cacheDriver;

        return $cache->get($this->cacheKey, $loader);
    }

    public function dispatcher(): Dispatcher
    {
        return new $this->dispatcher($this->buildConfiguration());
    }
}
