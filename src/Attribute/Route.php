<?php

declare(strict_types=1);

namespace Ahiru\Router\Attribute;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class Route
{
    /**
     * @phpstan-param 'GET'|'POST'|'PUT'|'PATCH'|'DELETE'|'OPTIONS' $method
     *
     * @param array<class-string<MiddlewareInterface>> $middlewares
     * @param array<string, string> $regex
     */
    public function __construct(
        protected string $method,
        protected string $path,
        protected string $name = '',
        protected array $middlewares = [],
        protected array $regex = [],
    ) {
    }

    /**
     * @phpstan-return 'GET'|'POST'|'PUT'|'PATCH'|'DELETE'|'OPTIONS'
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<class-string<MiddlewareInterface>>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @return array<string, string>
     */
    public function getRegex(): array
    {
        return $this->regex;
    }
}
