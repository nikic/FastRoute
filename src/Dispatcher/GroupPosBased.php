<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher\Result\Matched;

use function assert;
use function preg_match;

use const PREG_UNMATCHED_AS_NULL;

/** @final */
class GroupPosBased extends RegexBasedAbstract
{
    /** @inheritDoc */
    protected function dispatchVariableRoute(array $routeData, string $uri): ?Matched
    {
        foreach ($routeData as $data) {
            if (preg_match($data['regex'], $uri, $matches, PREG_UNMATCHED_AS_NULL) !== 1) {
                continue;
            }

            // find the first participating group; a group that matched the
            // empty string is '' and must not be skipped
            // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedFor
            for ($i = 1; $matches[$i] === null; ++$i) {
            }

            assert(isset($i));

            [$handler, $varNames, $extraParameters] = $data['routeMap'][$i];

            $vars = [];
            foreach ($varNames as $varName) {
                $value = $matches[$i++];
                assert($value !== null);

                $vars[$varName] = $value;
            }

            $result = new Matched();
            $result->handler = $handler;
            $result->variables = $vars;
            $result->extraParameters = $extraParameters;

            return $result;
        }

        return null;
    }
}
