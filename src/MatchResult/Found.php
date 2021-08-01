<?php

declare(strict_types=1);

namespace Ahiru\Router\MatchResult;

use Ahiru\Router\RouteDefinition;

class Found
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        protected RouteDefinition $route,
        protected array $attributes,
    ) {
    }

    public function getRoute(): RouteDefinition
    {
        return $this->route;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
