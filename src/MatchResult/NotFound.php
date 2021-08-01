<?php

declare(strict_types=1);

namespace Ahiru\Router\MatchResult;

final class NotFound
{
    public function __construct(
        protected string $path
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
