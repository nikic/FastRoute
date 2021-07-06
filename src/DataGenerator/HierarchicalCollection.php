<?php
declare(strict_types=1);

namespace FastRoute\DataGenerator;

use FastRoute\Route;
use FastRoute\RouteParser\Std;
use Generator;

use function array_key_exists;
use function array_values;
use function count;
use function preg_split;
use function str_replace;
use function strlen;
use function substr;
use function usort;

use const PREG_SPLIT_NO_EMPTY;

final class HierarchicalCollection
{
    private string $commonPrefix;

    /** @var list<Route|self> */
    private array $children;

    /** @param list<Route|self> $children */
    public function __construct(string $commonPrefix = '', array $children = [])
    {
        $this->commonPrefix = $commonPrefix;
        $this->children = $children;
    }

    /** @param list<Route> $routes */
    public static function organize(array $routes): self
    {
        usort(
            $routes,
            static function ($one, $other): int {
                $regexComparison = str_replace(Std::DEFAULT_DISPATCH_REGEX, '^', $one->regex) <=> str_replace(Std::DEFAULT_DISPATCH_REGEX, '^', $other->regex);

                if ($regexComparison !== 0) {
                    return $regexComparison;
                }

                return $one->httpMethod <=> $other->httpMethod;
            }
        );

        $list = new self();

        foreach ($routes as $route) {
            $list->addInHierarchy($route);
        }

        return $list;
    }

    private function addInHierarchy(Route $route, string $parentPrefix = ''): bool
    {
        if ($this->children === []) {
            $this->commonPrefix = $route->regex;
            $this->children[]  = $route;

            return true;
        }

        $fullPrefix = $parentPrefix . $this->commonPrefix;

        if ($fullPrefix === $route->regex) {
            $this->children[] = $route;

            return true;
        }

        $commonPrefix = $this->commonPrefix($fullPrefix, $route->regex);

        $commonPrefixLength = strlen($commonPrefix);
        $parentPrefixLength = strlen($parentPrefix);

        if ($commonPrefixLength <= $parentPrefixLength) {
            return false;
        }

        foreach ($this->children as $i => $child) {
            if ($child instanceof self && $child->addInHierarchy($route, $fullPrefix)) {
                return true;
            }

            if (! $child instanceof Route || $this->commonPrefix($child->regex, $route->regex) !== $child->regex) {
                continue;
            }

            $commonChildPrefix = substr($child->regex, strlen($fullPrefix));

            if ($commonChildPrefix !== '') {
                $this->children[$i] = new HierarchicalCollection($commonChildPrefix, [$child, $route]);

                return true;
            }
        }

        if ($commonPrefix === $fullPrefix) {
            $this->children[] = $route;

            return true;
        }

        $this->commonPrefix = substr($commonPrefix, $parentPrefixLength);

        if (count($this->children) === 1) {
            $this->children[] = $route;

            return true;
        }

        $this->children = [new self(substr($fullPrefix, $commonPrefixLength), $this->children), $route];

        return true;
    }

    private function commonPrefix(string $regexOne, string $regexTwo): string
    {
        $charsRegexOne = $this->chars($regexOne);
        $charsRegexTwo = $this->chars($regexTwo);

        $commonPrefix = '';

        while ($charsRegexOne->valid() && $charsRegexTwo->valid() && $charsRegexOne->current() === $charsRegexTwo->current()) {
            $commonPrefix .= $charsRegexOne->current();

            $charsRegexOne->next();
            $charsRegexTwo->next();
        }

        return $commonPrefix;
    }

    /** @return Generator<string> */
    private function chars(string $text): Generator
    {
        static $parsedItems;

        if ($parsedItems === null) {
            $parsedItems = [];
        }

        if (array_key_exists($text, $parsedItems)) {
            yield from $parsedItems[$text];

            return;
        }

        $items = [];

        $capturingGroups = 0;
        $buffer = '';

        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) as $char) {
            if ($char === '(') {
                ++$capturingGroups;
            }

            if ($capturingGroups <= 0) {
                yield $char;

                $items[] = $char;

                continue;
            }

            $buffer .= $char;

            if ($char === ')') {
                --$capturingGroups;
            }

            if ($capturingGroups !== 0 || $buffer === '') {
                continue;
            }

            yield $buffer;

            $items[] = $buffer;

            $buffer = '';
        }

        $parsedItems[$text] = $items;
    }

    /** @return list<array{regex: string, routeMap: array<string, array{0: array<string, mixed>, 1: list<string>}>}> */
    public function data(): array
    {
        $regex = '~^';
        $routeMap = [];

        $this->blah($regex, $routeMap);

        return [
            ['regex' => $regex . '$~', 'routeMap' => $routeMap],
        ];
    }

    /** @param array<string, array{0: array<string, mixed>, 1: list<string>}> $routeMap */
    private function blah(string &$regex, array &$routeMap, string &$markName = 'a', string $parentPrefix = ''): void
    {
        $regex .= $this->commonPrefix . '(?';

        $fullPrefix = $parentPrefix . $this->commonPrefix;
        $allocatedMarks = [];

        foreach ($this->children as $child) {
            if ($child instanceof self) {
                $regex .= '|';
                $child->blah($regex, $routeMap, $markName, $fullPrefix);

                continue;
            }

            if (isset($allocatedMarks[$child->regex])) {
                $routeMap[$allocatedMarks[$child->regex]][0][$child->httpMethod] = $child->handler;
                continue;
            }

            $allocatedMarks[$child->regex] = $markName;
            $routeMap[$markName] = [
                [$child->httpMethod => $child->handler],
                array_values($child->variables),
            ];

            $regex .= '|' . substr($child->regex, strlen($fullPrefix)) . '(*:' . $markName . ')';

            ++$markName;
        }

        $regex .= ')';
    }
}
