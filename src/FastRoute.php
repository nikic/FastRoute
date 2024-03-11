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
     * @param class-string<DataGenerator>    $dataGenerator
     * @param class-string<Dispatcher>       $dispatcher
     * @param class-string<ConfigureRoutes>  $routesConfiguration
     * @param class-string<GenerateUri>      $uriGenerator
     * @param Cache|class-string<Cache>|null $cacheDriver
     * @param non-empty-string|null          $cacheKey
     */
    public function __construct(
        private Closure $routeDefinitionCallback,
        private DataGenerator $dataGenerator,
        private Dispatcher $dispatcher,
        private ConfigureRoutes $routesConfiguration,
        private GenerateUri $uriGenerator,
        private ?Cache $cacheDriver,
        private ?string $cacheKey,
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
            new DataGenerator\MarkBased(),
            new Dispatcher\MarkBased(),
            new RouteCollector(new RouteParser\Std()),
            new GenerateUri\FromProcessedConfiguration,
            new FileCache(),
            $cacheKey,
        );
    }

    public function disableCache(): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->dataGenerator,
            $this->dispatcher,
            $this->routesConfiguration,
            $this->uriGenerator,
            null,
            null,
        );
    }

    /**
     * @param Cache|class-string<Cache> $driver
     * @param non-empty-string          $cacheKey
     */
    public function withCache(Cache $driver, string $cacheKey): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->dataGenerator,
            $this->dispatcher,
            $this->routesConfiguration,
            $this->uriGenerator,
            $driver,
            $cacheKey,
        );
    }

    public function useCharCountDispatcher(): self
    {
        return $this->useCustomDispatcher(new DataGenerator\CharCountBased(), new Dispatcher\CharCountBased());
    }

    public function useGroupCountDispatcher(): self
    {
        return $this->useCustomDispatcher(new DataGenerator\GroupCountBased(), new Dispatcher\GroupCountBased());
    }

    public function useGroupPosDispatcher(): self
    {
        return $this->useCustomDispatcher(new DataGenerator\GroupPosBased(), new Dispatcher\GroupPosBased());
    }

    public function useMarkDispatcher(): self
    {
        return $this->useCustomDispatcher(new DataGenerator\MarkBased(), new Dispatcher\MarkBased());
    }

    /**
     * @param class-string<DataGenerator> $dataGenerator
     * @param class-string<Dispatcher>    $dispatcher
     */
    public function useCustomDispatcher(DataGenerator $dataGenerator, Dispatcher $dispatcher): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $dataGenerator,
            $dispatcher,
            $this->routesConfiguration,
            $this->uriGenerator,
            $this->cacheDriver,
            $this->cacheKey,
        );
    }

    /** @param class-string<GenerateUri> $uriGenerator */
    public function withUriGenerator(GenerateUri $uriGenerator): self
    {
        return new self(
            $this->routeDefinitionCallback,
            $this->dataGenerator,
            $this->dispatcher,
            $this->routesConfiguration,
            $uriGenerator,
            $this->cacheDriver,
            $this->cacheKey,
        );
    }

    /** @return ProcessedData */
    private function buildConfiguration(): array
    {
        if ($this->processedConfiguration !== null) {
            return $this->processedConfiguration;
        }

        $loader = function (): array {
            ($this->routeDefinitionCallback)($this->routesConfiguration);
            return $this->routesConfiguration->processedRoutes($this->dataGenerator);
        };

        if ($this->cacheDriver === null) {
            return $this->processedConfiguration = $loader();
        }

        assert(is_string($this->cacheKey));

        return $this->processedConfiguration = $this->cacheDriver->get($this->cacheKey, $loader);
    }

    public function dispatcher(): Dispatcher
    {
        return $this->dispatcher->with($this->buildConfiguration());
    }

    public function uriGenerator(): GenerateUri
    {
        return $this->uriGenerator->with($this->buildConfiguration()[2]);
    }
}
