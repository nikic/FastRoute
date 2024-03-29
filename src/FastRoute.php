<?php
declare(strict_types=1);

namespace FastRoute;

use Closure;

use function assert;
use function is_string;

/** @phpstan-import-type ProcessedData from ConfigureRoutes */
final class FastRoute
{
    /** @var ProcessedData|null */
    private ?array $processedConfiguration = null;

    /**
     * @param Closure(ConfigureRoutes):void $routeDefinitionCallback
     * @param non-empty-string|null         $cacheKey
     */
    public function __construct(
        private Closure $routeDefinitionCallback,
        private ?string $cacheKey,
        private Settings $settings = new FastSettings(),
    ) {
    }

    /** @return ProcessedData */
    private function buildConfiguration(): array
    {
        if ($this->processedConfiguration !== null) {
            return $this->processedConfiguration;
        }

        $loader = function (): array {
            $routesConfiguration = $this->settings->getRoutesConfiguration();
            ($this->routeDefinitionCallback)($routesConfiguration);

            return $routesConfiguration->processedRoutes($this->settings->getDataGenerator());
        };

        $cacheDriver = $this->settings->getCacheDriver();

        if ($cacheDriver === null) {
            return $this->processedConfiguration = $loader();
        }

        assert(is_string($this->cacheKey));

        return $this->processedConfiguration = $cacheDriver->get($this->cacheKey, $loader);
    }

    public function dispatcher(): Dispatcher
    {
        return $this->settings->getDispatcher()->with($this->buildConfiguration());
    }

    public function uriGenerator(): GenerateUri
    {
        return $this->settings->getUriGenerator()->with($this->buildConfiguration()[2]);
    }
}
