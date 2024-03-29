<?php
declare(strict_types=1);

namespace FastRoute;

interface Settings
{
    public function getRouteParser(): RouteParser;

    public function getDataGenerator(): DataGenerator;

    public function getDispatcher(): Dispatcher;

    public function getRoutesConfiguration(): ConfigureRoutes;

    public function getUriGenerator(): GenerateUri;

    public function getCacheDriver(): ?Cache;
}
