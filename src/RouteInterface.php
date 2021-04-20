<?php
declare(strict_types=1);

namespace FastRoute;

interface RouteInterface
{
    /**
     * Tests whether this route matches the given string.
     */
    public function matches(string $string): bool;

    /**
     * The handler for a route
     *
     * @return mixed
     */
    public function handler();

    public function regex(): string;

    /**
     * @return mixed[]
     */
    public function variables(): array;
}
