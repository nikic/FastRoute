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
     * @param class-string<ConfigureRoutes>  $configureRoutes
     * @param class-string<GenerateUri>      $uriGenerator
     * @param Cache|class-string<Cache>|null $cacheDriver
     * @param non-empty-string|null          $cacheKey
     */
    private function __construct(
        private readonly Closure $routeDefinitionCallback,
        private readonly string $routeParser,
        private readonly string $dataGenerator,
        private readonly string $dispatcher,
        private readonly string $configureRoutes,
        private readonly string $uriGenerator,
        private readonly Cache|string|null $cacheDriver,
        private readonly ?string $cacheKey,
    ) {
    }

    /**
     * @param Closure(ConfigureRoutes):void $routeDefinitionCallback
     * @param non-empty-string              $cacheKey
     */
    public static function recommendedSettings(Closure $routeDefinitionCallback, string $cacheKey): self
    {
        return new self(
            $routeDefinitionCallback,
            RouteParser\Std::class,
            DataGenerator\MarkBased::class,
            Dispatcher\MarkBased::class,
            RouteCollector::class,
            GenerateUri\FromProcessedConfiguration::class,
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
            $this->configureRoutes,
            $this->uriGenerator,
            null,
            null,
        );
    }

    /**
     * @param Cache|class-string<Cache> $driver
     * @param non-empty-string          $cacheKey
     */
    public function withCache(Cache|string $driver, string $cacheKey): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->routeParser,
            $this->dataGenerator,
            $this->dispatcher,
            $this->configureRoutes,
            $this->uriGenerator,
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
            $this->configureRoutes,
            $this->uriGenerator,
            $this->cacheDriver,
            $this->cacheKey,
        );
    }

    /**
     * @param class-string<ConfigureRoutes> $configureRoutes
     */
    public function useCustomConfigureRoutes(string $configureRoutes): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->routeParser,
            $this->dataGenerator,
            $this->dispatcher,
            $configureRoutes,
            $this->uriGenerator,
            $this->cacheDriver,
            $this->cacheKey,
        );
    }

    /** @param class-string<GenerateUri> $uriGenerator */
    public function withUriGenerator(string $uriGenerator): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->routeParser,
            $this->dataGenerator,
            $this->dispatcher,
            $this->configureRoutes,
            $uriGenerator,
            $this->cacheDriver,
            $this->cacheKey,
        );
    }

    /** @return ProcessedData */
    public function processedConfiguration(): array
    {
        if ($this->processedConfiguration !== null) {
            return $this->processedConfiguration;
        }

        $loader = function (): array {
            $configureRoutes = $this->configureRoutes();
            ($this->routeDefinitionCallback)($configureRoutes);
            return $configureRoutes->processedRoutes();
        };

        if ($this->cacheDriver === null) {
            return $this->processedConfiguration = $loader();
        }

        assert(is_string($this->cacheKey));

        $cache = is_string($this->cacheDriver)
            ? new $this->cacheDriver()
            : $this->cacheDriver;

        return $this->processedConfiguration = $cache->get($this->cacheKey, $loader);
    }

    public function configureRoutes(): ConfigureRoutes
    {
        return new $this->configureRoutes(
            new $this->routeParser(),
            new $this->dataGenerator(),
        );
    }

    public function dispatcher(): Dispatcher
    {
        return new $this->dispatcher($this->processedConfiguration());
    }

    public function uriGenerator(): GenerateUri
    {
        return new $this->uriGenerator($this->processedConfiguration()[2]);
    }
}
