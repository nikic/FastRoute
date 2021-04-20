<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use ArrayAccess;

/**
 * Result Interface
 */
interface ResultInterface extends ArrayAccess
{
    /**
     * @return mixed
     */
    public function handler();

    public function status(): int;

    /**
     * @return mixed
     */
    public function args();

    public function routeMatched(): bool;

    public function methodNotAllowed(): bool;

    public function routeNotFound(): bool;
}
