<?php

declare(strict_types=1);

namespace Ahiru\Router\Cache;

use Ahiru\Router\RouteCollection;

interface CacheDriver
{
    public function store(RouteCollection $routes): void;

    public function restore(): ?RouteCollection;

    public function delete(): void;
}
