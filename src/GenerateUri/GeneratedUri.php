<?php
declare(strict_types=1);

namespace FastRoute\GenerateUri;

use FastRoute\GenerateUri;
use Psr\Http\Message\UriInterface;
use Stringable;

use function http_build_query;

/** @phpstan-import-type UriSubstitutions from GenerateUri */
final class GeneratedUri implements Stringable
{
    /**
     * @param non-empty-string $path
     * @param UriSubstitutions $unmatchedSubstitutions
     */
    public function __construct(
        public readonly string $path,
        public readonly array $unmatchedSubstitutions,
    ) {
    }

    public function asUri(UriInterface $baseUri): UriInterface
    {
        return $baseUri
            ->withPath($this->path)
            ->withQuery(http_build_query($this->unmatchedSubstitutions));
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
