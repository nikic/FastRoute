<?php
declare(strict_types=1);

namespace FastRoute\Test;

use FastRoute\Cache;
use FastRoute\ConfigureRoutes;
use FastRoute\Dispatcher;
use FastRoute\FastSettings;
use FastRoute\FastRoute;
use FastRoute\GenerateUri;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FastRouteTest extends TestCase
{
    #[PHPUnit\Test]
    public function markShouldBeTheDefaultDispatcher(): void
    {
        $dispatcher = (new FastRoute(
            self::routes(...),
            'test',
            FastSettings::recommended()
                ->disableCache(),
        ))->dispatcher();

        self::assertInstanceOf(Dispatcher\MarkBased::class, $dispatcher);
    }

    #[PHPUnit\Test]
    public function canBeConfiguredToUseCharCountDispatcher(): void
    {
        $dispatcher = (new FastRoute(
            self::routes(...),
            'test',
            FastSettings::recommended()
                ->disableCache()
                ->useCharCountDispatcher(),
        ))->dispatcher();

        self::assertInstanceOf(Dispatcher\CharCountBased::class, $dispatcher);
    }

    #[PHPUnit\Test]
    public function canBeConfiguredToUseGroupPosDispatcher(): void
    {
        $dispatcher = (new FastRoute(
            self::routes(...),
            'test',
            FastSettings::recommended()
                ->disableCache()
                ->useGroupPosDispatcher(),
        ))->dispatcher();

        self::assertInstanceOf(Dispatcher\GroupPosBased::class, $dispatcher);
    }

    #[PHPUnit\Test]
    public function canBeConfiguredToUseGroupCountDispatcher(): void
    {
        $dispatcher = (new FastRoute(
            self::routes(...),
            'test',
            FastSettings::recommended()
                ->disableCache()
                ->useGroupCountDispatcher(),
        ))->dispatcher();

        self::assertInstanceOf(Dispatcher\GroupCountBased::class, $dispatcher);
    }

    #[PHPUnit\Test]
    public function canBeConfiguredToUseMarkDispatcher(): void
    {
        $dispatcher = (new FastRoute(
            self::routes(...),
            'test',
            FastSettings::recommended()
                ->disableCache()
                ->useCharCountDispatcher()
                ->useMarkDispatcher(),
        ))->dispatcher();

        self::assertInstanceOf(Dispatcher\MarkBased::class, $dispatcher);
    }

    #[PHPUnit\Test]
    public function canBeConfiguredToUseCustomCache(): void
    {
        $cache = new class () implements Cache {
            /** @inheritDoc */
            public function get(string $key, callable $loader): array
            {
                if ($key === 'test') {
                    return [['GET' => ['/' => ['test2', ['test' => true]]]], [], []];
                }

                throw new RuntimeException('This dummy implementation is not meant for other cases');
            }
        };

        $dispatcher = (new FastRoute(
            self::routes(...),
            'test',
            FastSettings::recommended()
                ->withCache($cache),
        ))->dispatcher();

        $result = $dispatcher->dispatch('GET', '/');

        self::assertInstanceOf(Dispatcher\Result\Matched::class, $result);
        // self::assertSame('test2', $result->handler); // should use data from cache, not from loader
        self::assertSame(['test' => true], $result->extraParameters); // should use data from cache, not from loader
    }

    private static function routes(ConfigureRoutes $collector): void
    {
        $collector->get('/', 'test');
    }

    #[PHPUnit\Test]
    public function defaultUriGeneratorMustBeProvided(): void
    {
        $uriGenerator = (new FastRoute(
            self::routes(...),
            'test',
        ))->uriGenerator();

        self::assertInstanceOf(GenerateUri\FromProcessedConfiguration::class, $uriGenerator);
    }

    #[PHPUnit\Test]
    public function uriGeneratorCanBeOverridden(): void
    {
        $generator = new class () implements GenerateUri {
            /** @inheritDoc */
            public function forRoute(string $name, array $substitutions = []): string
            {
                return '';
            }

            /** @inheritdoc */
            public function with(array $processedConfiguration): self
            {
                return clone $this;
            }
        };

        $uriGenerator = (new FastRoute(
            self::routes(...),
            'test',
            FastSettings::recommended()
                ->disableCache()
                ->withUriGenerator($generator),
        ))->uriGenerator();

        self::assertInstanceOf($generator::class, $uriGenerator);
    }

    #[PHPUnit\Test]
    public function processedDataShouldOnlyBeBuiltOnce(): void
    {
        $loader = static function (ConfigureRoutes $routes): void {
            $routes->addRoute(
                ['GET', 'POST'],
                '/users/{name}',
                'do-stuff',
                [ConfigureRoutes::ROUTE_NAME => 'users'],
            );

            $routes->get('/posts/{id}', 'fetchPosts', [ConfigureRoutes::ROUTE_NAME => 'posts.fetch']);

            $routes->get(
                '/articles/{year}[/{month}[/{day}]]',
                'fetchArticle',
                [ConfigureRoutes::ROUTE_NAME => 'articles.fetch'],
            );
        };

        $fastRoute = new FastRoute(
            $loader,
            'test',
            FastSettings::recommended()->disableCache(),
        );

        $dispatcher   = $fastRoute->dispatcher();
        $uriGenerator = $fastRoute->uriGenerator();

        self::assertInstanceOf(Dispatcher\Result\Matched::class, $dispatcher->dispatch('GET', '/users/lcobucci'));
        self::assertInstanceOf(Dispatcher\Result\Matched::class, $dispatcher->dispatch('POST', '/users/lcobucci'));
        self::assertInstanceOf(Dispatcher\Result\Matched::class, $dispatcher->dispatch('GET', '/posts/1234'));

        self::assertSame('/users/lcobucci', $uriGenerator->forRoute('users', ['name' => 'lcobucci']));
        self::assertSame('/posts/1234', $uriGenerator->forRoute('posts.fetch', ['id' => '1234']));
        self::assertSame('/articles/2024', $uriGenerator->forRoute('articles.fetch', ['year' => '2024']));
        self::assertSame('/articles/2024/02', $uriGenerator->forRoute('articles.fetch', ['year' => '2024', 'month' => '02']));
        self::assertSame('/articles/2024/02/15', $uriGenerator->forRoute('articles.fetch', ['year' => '2024', 'month' => '02', 'day' => '15']));
    }
}
