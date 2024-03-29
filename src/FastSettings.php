<?php
declare(strict_types=1);

namespace FastRoute;

use FastRoute\Cache\FileCache;

use function is_string;

/** @phpstan-import-type ProcessedData from ConfigureRoutes */
final class FastSettings implements Settings
{
    /**
     * @param RouteParser|class-string<RouteParser>         $routeParser
     * @param DataGenerator|class-string<DataGenerator>     $dataGenerator
     * @param Dispatcher|class-string<Dispatcher>           $dispatcher
     * @param ConfigureRoutes|class-string<ConfigureRoutes> $routesConfiguration
     * @param GenerateUri|class-string<GenerateUri>         $uriGenerator
     * @param Cache|class-string<Cache>|null                $cacheDriver
     */
    public function __construct(
        private RouteParser|string $routeParser = RouteParser\Std::class,
        private DataGenerator|string $dataGenerator = DataGenerator\MarkBased::class,
        private Dispatcher|string $dispatcher = Dispatcher\MarkBased::class,
        private ConfigureRoutes|string $routesConfiguration = RouteCollector::class,
        private GenerateUri|string $uriGenerator = GenerateUri\FromProcessedConfiguration::class,
        private Cache|string|null $cacheDriver = FileCache::class,
    ) {
    }

    public function getRouteParser(): RouteParser
    {
        if (is_string($this->routeParser)) {
            $this->routeParser = new $this->routeParser();
        }

        return $this->routeParser;
    }

    public function getDataGenerator(): DataGenerator
    {
        if (is_string($this->dataGenerator)) {
            $this->dataGenerator = new $this->dataGenerator();
        }

        return $this->dataGenerator;
    }

    public function getDispatcher(): Dispatcher
    {
        if (is_string($this->dispatcher)) {
            $this->dispatcher = new $this->dispatcher();
        }

        return $this->dispatcher;
    }

    public function getRoutesConfiguration(): ConfigureRoutes
    {
        if (is_string($this->routesConfiguration)) {
            $this->routesConfiguration = new $this->routesConfiguration(
                $this->getRouteParser(),
            );
        }

        return $this->routesConfiguration;
    }

    public function getUriGenerator(): GenerateUri
    {
        if (is_string($this->uriGenerator)) {
            $this->uriGenerator = new $this->uriGenerator();
        }

        return $this->uriGenerator;
    }

    public function getCacheDriver(): ?Cache
    {
        if (is_string($this->cacheDriver)) {
            $this->cacheDriver = new $this->cacheDriver();
        }

        return $this->cacheDriver;
    }

    public static function recommended(): self
    {
        return new self(
            RouteParser\Std::class,
            DataGenerator\MarkBased::class,
            Dispatcher\MarkBased::class,
            RouteCollector::class,
            GenerateUri\FromProcessedConfiguration::class,
            FileCache::class,
        );
    }

    public function disableCache(): self
    {
        return new self(
            $this->routeParser,
            $this->dataGenerator,
            $this->dispatcher,
            $this->routesConfiguration,
            $this->uriGenerator,
            null,
        );
    }

    /** @param Cache|class-string<Cache> $driver */
    public function withCache(Cache|string $driver): self
    {
        return new self(
            $this->routeParser,
            $this->dataGenerator,
            $this->dispatcher,
            $this->routesConfiguration,
            $this->uriGenerator,
            $driver,
        );
    }

    public function useCharCountDispatcher(): self
    {
        return $this->useCustomDispatcher(
            DataGenerator\CharCountBased::class,
            Dispatcher\CharCountBased::class,
        );
    }

    public function useGroupCountDispatcher(): self
    {
        return $this->useCustomDispatcher(
            DataGenerator\GroupCountBased::class,
            Dispatcher\GroupCountBased::class,
        );
    }

    public function useGroupPosDispatcher(): self
    {
        return $this->useCustomDispatcher(
            DataGenerator\GroupPosBased::class,
            Dispatcher\GroupPosBased::class,
        );
    }

    public function useMarkDispatcher(): self
    {
        return $this->useCustomDispatcher(
            DataGenerator\MarkBased::class,
            Dispatcher\MarkBased::class,
        );
    }

    /**
     * @param DataGenerator|class-string<DataGenerator> $dataGenerator
     * @param Dispatcher|class-string<Dispatcher>       $dispatcher
     */
    public function useCustomDispatcher(DataGenerator|string $dataGenerator, Dispatcher|string $dispatcher): self
    {
        return new self(
            $this->routeParser,
            $dataGenerator,
            $dispatcher,
            $this->routesConfiguration,
            $this->uriGenerator,
            $this->cacheDriver,
        );
    }

    /** @param GenerateUri|class-string<GenerateUri> $uriGenerator */
    public function withUriGenerator(GenerateUri|string $uriGenerator): self
    {
        return new self(
            $this->routeParser,
            $this->dataGenerator,
            $this->dispatcher,
            $this->routesConfiguration,
            $uriGenerator,
            $this->cacheDriver,
        );
    }
}
