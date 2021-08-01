<?php

declare(strict_types=1);

namespace Ahiru\Router;

use Ahiru\Router\Path\Path;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouteDefinition
{
    /**
     * @phpstan-return class-string<RequestHandlerInterface>
     */
    public function getRequestHandler(): string;

    /**
     * @phpstan-return 'GET'|'POST'|'PATCH'|'PUT'|'DELETE'|'OPTIONS'
     */
    public function getMethod(): string;

    public function getPath(): Path;

    public function getName(): string;

    public function hasName(): bool;

    /**
     * @return array<string, string>
     */
    public function getRegex(): array;

    /**
     * @return array<string, string>
     */
    public function getAttributes(string $path): array;

    /**
     * @return array<class-string<MiddlewareInterface>>
     */
    public function getMiddlewares(): array;
}
