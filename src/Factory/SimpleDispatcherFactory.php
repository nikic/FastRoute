<?php
declare(strict_types=1);

namespace FastRoute\Factory;

use FastRoute\DataGenerator\MarkBasedProcessor;
use FastRoute\DataGenerator\RegexBased;
use FastRoute\Dispatcher;
use FastRoute\RouteCollection;
use FastRoute\RouteParser\Std;

use function assert;
use function is_string;

class SimpleDispatcherFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public static function make(callable $routeDefinitionCallback, array $options = []): Dispatcher
    {
        $options += [
            'routeParser' => Std::class,
            'dataGenerator' => new RegexBased(new MarkBasedProcessor()),
            'dispatcher' => Dispatcher\MarkBased::class,
            'routeCollector' => RouteCollection::class,
        ];

        $routeCollector = new $options['routeCollector'](
            is_string($options['routeParser']) ? new $options['routeParser']() : $options['routeParser'],
            is_string($options['dataGenerator']) ? new $options['dataGenerator']() : $options['dataGenerator']
        );
        assert($routeCollector instanceof RouteCollection);
        $routeDefinitionCallback($routeCollector);

        return new $options['dispatcher']($routeCollector->getData());
    }
}
