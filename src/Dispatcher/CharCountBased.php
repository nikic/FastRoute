<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use function assert;
use function end;
use function preg_match;

class CharCountBased extends RegexBasedAbstract
{
    /** @inheritDoc */
    protected function dispatchVariableRoute(array $routeData, string $uri): ?array
    {
        foreach ($routeData as $data) {
            assert(isset($data['suffix']));

            if (preg_match($data['regex'], $uri . $data['suffix'], $matches) !== 1) {
                continue;
            }

            [$handler, $varNames] = $data['routeMap'][end($matches)];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }

            return [self::FOUND, $handler, $vars];
        }

        return null;
    }
}
