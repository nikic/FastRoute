<?php
declare(strict_types=1);

namespace FastRoute;

use LogicException;
use RuntimeException;
use function file_exists;
use function file_put_contents;
use function function_exists;
use function is_array;
use function var_export;

if (! function_exists('FastRoute\simpleDispatcher')) {

    /**
     * @param array<string, string> $options
     *
     * @psalm-param array{
     *  routeParser?:class-string<RouteParser>,
     *  dataGenerator?:class-string<DataGenerator>,
     *  dispatcher?:class-string<Dispatcher>,
     *  routeCollector?:class-string<RouteCollector>,
     * } $options
     */
    function simpleDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        $options += [
            'routeParser' => RouteParser\Std::class,
            'dataGenerator' => DataGenerator\GroupCountBased::class,
            'dispatcher' => Dispatcher\GroupCountBased::class,
            'routeCollector' => RouteCollector::class,
        ];

        /**
         * @psalm-var array{
         *  routeParser:class-string<RouteParser>,
         *  dataGenerator:class-string<DataGenerator>,
         *  dispatcher:class-string<Dispatcher>,
         *  routeCollector:class-string<RouteCollector>,
         * }
         */
        $options = $options;

        /** @var RouteCollector $routeCollector */
        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'](), new $options['dataGenerator']()
        );
        $routeDefinitionCallback($routeCollector);

        return new $options['dispatcher']($routeCollector->getData());
    }

    /**
     * @param array<string, string> $options
     *
     * @psalm-param array{
     *  routeParser?:class-string<RouteParser>,
     *  dataGenerator?:class-string<DataGenerator>,
     *  dispatcher?:class-string<Dispatcher>,
     *  routeCollector?:class-string<RouteCollector>,
     *  cacheDisabled?:bool,
     *  cacheFile?:string,
     * } $options
     */
    function cachedDispatcher(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        $options += [
            'routeParser' => RouteParser\Std::class,
            'dataGenerator' => DataGenerator\GroupCountBased::class,
            'dispatcher' => Dispatcher\GroupCountBased::class,
            'routeCollector' => RouteCollector::class,
            'cacheDisabled' => false,
        ];

        /**
         * @psalm-var array{
         *  routeParser:class-string<RouteParser>,
         *  dataGenerator:class-string<DataGenerator>,
         *  dispatcher:class-string<Dispatcher>,
         *  routeCollector:class-string<RouteCollector>,
         *  cacheDisabled:bool,
         *  cacheFile?:string,
         * }
         */
        $options = $options;

        if (! isset($options['cacheFile'])) {
            throw new LogicException('Must specify "cacheFile" option');
        }

        if (! $options['cacheDisabled'] && file_exists($options['cacheFile'])) {
            $dispatchData = require $options['cacheFile'];
            if (! is_array($dispatchData)) {
                throw new RuntimeException('Invalid cache file "' . $options['cacheFile'] . '"');
            }

            /**
             * @psalm-var Dispatcher
             */
            return new $options['dispatcher']($dispatchData);
        }

        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'](), new $options['dataGenerator']()
        );
        $routeDefinitionCallback($routeCollector);

        /** @var RouteCollector $routeCollector */
        $dispatchData = $routeCollector->getData();
        if (! $options['cacheDisabled']) {
            file_put_contents(
                $options['cacheFile'],
                '<?php return ' . var_export($dispatchData, true) . ';'
            );
        }

        /**
         * @psalm-var Dispatcher
         */
        return new $options['dispatcher']($dispatchData);
    }

}
