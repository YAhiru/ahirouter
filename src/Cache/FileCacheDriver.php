<?php

declare(strict_types=1);

namespace Ahiru\Router\Cache;

use Ahiru\Router\RouteCollection;
use LogicException;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function mkdir;
use function serialize;
use function sprintf;
use function unlink;
use function unserialize;

class FileCacheDriver implements CacheDriver
{
    public function __construct(
        protected string $path,
    ) {
    }

    public function store(RouteCollection $routes): void
    {
        $dir = dirname($this->path);
        /** @infection-ignore-all */
        ! file_exists($dir) && mkdir($dir, 0777, true);

        file_put_contents($this->path, serialize($routes));
    }

    public function restore(): ?RouteCollection
    {
        $content = @file_get_contents($this->path);

        if (false === $content) {
            return null;
        }
        $routes = unserialize($content);

        if (! $routes instanceof RouteCollection) {
            throw new LogicException(sprintf('"%s" is invalid content.', $this->path));
        }

        return $routes;
    }

    public function delete(): void
    {
        @unlink($this->path);
    }
}
