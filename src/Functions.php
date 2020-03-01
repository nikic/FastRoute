<?php
declare(strict_types=1);

namespace FastRoute;

use LogicException;
use RuntimeException;
use function assert;
use function file_exists;
use function file_put_contents;
use function is_array;
use function var_export;

class Functions
{
    /**
     * @param array<string, string> $options
     */
    public static function simpleDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        $options += [
            'routeParser' => RouteParser\Std::class,
            'dataGenerator' => DataGenerator\GroupCountBased::class,
            'dispatcher' => Dispatcher\GroupCountBased::class,
            'routeCollector' => RouteCollector::class,
        ];

        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'](), new $options['dataGenerator']()
        );
        assert($routeCollector instanceof RouteCollector);
        $routeDefinitionCallback($routeCollector);

        return new $options['dispatcher']($routeCollector->getData());
    }
    /**
     * @param array<string, string> $options
     */
    public static function cachedDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        $options += [
            'routeParser' => RouteParser\Std::class,
            'dataGenerator' => DataGenerator\GroupCountBased::class,
            'dispatcher' => Dispatcher\GroupCountBased::class,
            'routeCollector' => RouteCollector::class,
            'cacheDisabled' => false,
        ];

        if (! isset($options['cacheFile'])) {
            throw new LogicException('Must specify "cacheFile" option');
        }

        if (! $options['cacheDisabled'] && file_exists($options['cacheFile'])) {
            $dispatchData = require $options['cacheFile'];
            if (! is_array($dispatchData)) {
                throw new RuntimeException('Invalid cache file "' . $options['cacheFile'] . '"');
            }

            return new $options['dispatcher']($dispatchData);
        }

        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'](), new $options['dataGenerator']()
        );
        assert($routeCollector instanceof RouteCollector);
        $routeDefinitionCallback($routeCollector);

        $dispatchData = $routeCollector->getData();
        if (! $options['cacheDisabled']) {
            file_put_contents(
                $options['cacheFile'],
                '<?php return ' . var_export($dispatchData, true) . ';'
            );
        }

        return new $options['dispatcher']($dispatchData);
    }
}
