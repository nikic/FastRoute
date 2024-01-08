<?php
declare(strict_types=1);

namespace FastRoute\Cache;

use FastRoute\Cache;

use function apcu_add;
use function apcu_fetch;
use function is_array;

final class ApcuCache implements Cache
{
    /** @inheritdoc */
    public function get(string $key, callable $loader): array
    {
        $result = apcu_fetch($key, $itemFetched);

        if ($itemFetched && is_array($result)) {
            // @phpstan-ignore-next-line because we won´t be able to validate the array shape in a performant way
            return $result;
        }

        $data = $loader();
        apcu_add($key, $data);

        return $data;
    }
}
