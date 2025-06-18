<?php
declare(strict_types=1);

namespace FastRoute\Test\GenerateUri;

use FastRoute\GenerateUri;
use FastRoute\RouteParser;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

use function array_map;
use function array_reverse;

/** @phpstan-import-type ParsedRoutes from RouteParser */
final class FromProcessedConfigurationTest extends TestCase
{
    #[PHPUnit\Test]
    public function undefinedRoutesCannotHaveTheirUriGenerated(): void
    {
        $generator = self::routeGeneratorFor(['post.fetch' => '/post/{id:\d+}']);

        $this->expectException(GenerateUri\UriCouldNotBeGenerated::class);
        $this->expectExceptionMessage('There is no route with name "test" defined');

        $generator->forRoute('test');
    }

    #[PHPUnit\Test]
    public function itCannotGenerateUriWhenNoneOfTheRequiredParametersAreGiven(): void
    {
        $generator = self::routeGeneratorFor(['post.fetch' => '/post/{id:\d+}']);

        $this->expectException(GenerateUri\UriCouldNotBeGenerated::class);
        $this->expectExceptionMessage('Route "post.fetch" expects at least parameter values for [id], but received none');

        $generator->forRoute('post.fetch');
    }

    #[PHPUnit\Test]
    public function itCannotGenerateUriWhenIrrelevantParametersAreGiven(): void
    {
        $generator = self::routeGeneratorFor(['post.fetch' => '/post/{id:\d+}']);

        $this->expectException(GenerateUri\UriCouldNotBeGenerated::class);
        $this->expectExceptionMessage('Route "post.fetch" expects at least parameter values for [id], but received [name,age]');

        $generator->forRoute('post.fetch', ['name' => 'me', 'age' => '1234']);
    }

    #[PHPUnit\Test]
    public function argumentValidationMustBeRespected(): void
    {
        $generator = self::routeGeneratorFor(['post.fetch' => '/post/{id:\d+}']);

        $this->expectException(GenerateUri\UriCouldNotBeGenerated::class);
        $this->expectExceptionMessage('Route "post.fetch" expects the parameter [id] to match the regex `\d+`');

        $generator->forRoute('post.fetch', ['id' => 'test']);
    }

    #[PHPUnit\Test]
    public function routesWithOptionalSegmentsCanBeGenerated(): void
    {
        $generator = self::routeGeneratorFor(['archive.fetch' => '/archive/{username}[/{year}[/{month}[/{day}]]]']);

        self::assertEquals(
            '/archive/test',
            $generator->forRoute('archive.fetch', ['username' => 'test']),
        );

        self::assertEquals(
            '/archive/test/2024',
            $generator->forRoute('archive.fetch', ['username' => 'test', 'year' => '2024']),
        );

        self::assertEquals(
            '/archive/test/2024/02',
            $generator->forRoute('archive.fetch', ['username' => 'test', 'year' => '2024', 'month' => '02']),
        );

        self::assertEquals(
            '/archive/test/2024/02/01',
            $generator->forRoute(
                'archive.fetch',
                ['username' => 'test', 'year' => '2024', 'month' => '02', 'day' => '01'],
            ),
        );
    }

    #[PHPUnit\Test]
    public function staticRoutesCanAlsoBeGenerated(): void
    {
        $generator = self::routeGeneratorFor(['post.fetch-special' => '/post/a-special-post']);

        self::assertEquals('/post/a-special-post', $generator->forRoute('post.fetch-special'));
    }

    #[PHPUnit\Test]
    public function resultingUriMustNotHaveUrlEncodedParameters(): void
    {
        $generator = self::routeGeneratorFor(['post.fetch' => '/post/{id}']);

        self::assertEquals(
            '/post/@something-that needs to be encoded 😁',
            $generator->forRoute('post.fetch', ['id' => '@something-that needs to be encoded 😁']),
        );
    }

    #[PHPUnit\Test]
    public function urlEncodedParametersShouldNotBeManipulated(): void
    {
        $generator = self::routeGeneratorFor(['post.fetch' => '/post/{id}']);

        self::assertEquals(
            '/post/%40something%20that%20needs%20to%20be%20encoded%20%F0%9F%98%81',
            $generator->forRoute('post.fetch', ['id' => '%40something%20that%20needs%20to%20be%20encoded%20%F0%9F%98%81']),
        );
    }

    #[PHPUnit\Test]
    public function nonProcessedParametersAreRetrievable(): void
    {
        $generator = self::routeGeneratorFor(['post.fetch' => '/post/{id}']);
        $generatedUri = $generator->forRoute('post.fetch', ['id' => 'testing', 'foo' => 'bar', 'baz' => 'foo']);

        self::assertSame('/post/testing', $generatedUri->path);
        self::assertSame(['foo' => 'bar', 'baz' => 'foo'], $generatedUri->unmatchedSubstitutions);
        self::assertSame(
            'https://api.my-company.dev:8080/post/testing?foo=bar&baz=foo',
            (string) $generatedUri->asUri(
                (new Uri())
                    ->withScheme('https')
                    ->withHost('api.my-company.dev')
                    ->withPort(8080),
            ),
        );
    }

    #[PHPUnit\Test]
    public function unicodeParametersAreAlsoAccepted(): void
    {
        $generator = self::routeGeneratorFor(['post.fetch' => '/post/{id:[\w\-\%]+}']);

        self::assertEquals('/post/bar-測試', $generator->forRoute('post.fetch', ['id' => 'bar-測試']));
    }

    /** @param non-empty-array<non-empty-string, non-empty-string> $routeMap */
    private static function routeGeneratorFor(array $routeMap): GenerateUri
    {
        $parseRoutes = static function (string $route): array {
            return array_reverse((new RouteParser\Std())->parse($route));
        };

        $protoGenerateUri = new GenerateUri\FromProcessedConfiguration();
        return $protoGenerateUri->with(array_map($parseRoutes, $routeMap));
    }
}
