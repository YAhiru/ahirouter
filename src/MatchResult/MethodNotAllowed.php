<?php

declare(strict_types=1);

namespace Ahiru\Router\MatchResult;

class MethodNotAllowed
{
    /**
     * @phpstan-param non-empty-list<'GET'|'POST'|'PATCH'|'PUT'|'DELETE'|'OPTIONS'> $allowMethods
     */
    public function __construct(
        protected array $allowMethods
    ) {
    }

    /**
     * @phpstan-return non-empty-list<'GET'|'POST'|'PATCH'|'PUT'|'DELETE'|'OPTIONS'>
     *
     * @return array<string>
     */
    public function getAllowMethods(): array
    {
        return $this->allowMethods;
    }
}
