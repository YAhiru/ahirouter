<?php

declare(strict_types=1);

namespace Ahiru\Router;

use Psr\Http\Server\MiddlewareInterface;

interface RouteDefiner
{
    /**
     * @phpstan-param class-string<MiddlewareInterface> $middleware
     */
    public function addMiddleware(string $middleware): self;

    /**
     * @param array<class-string<MiddlewareInterface>> $middlewares
     *
     * @return $this
     */
    public function addMiddlewares(array $middlewares): self;

    /**
     * @param class-string<MiddlewareInterface> $middleware
     *
     * @return $this
     */
    public function removeMiddleware(string $middleware): self;

    /**
     * @param array<class-string<MiddlewareInterface>> $middlewares
     *
     * @return $this
     */
    public function removeMiddlewares(array $middlewares): self;

    /**
     * @param array<string, string> $regexes
     *
     * @return $this
     */
    public function setRegex(array $regexes): self;

    /**
     * @return $this
     */
    public function setName(string $name): self;
}
