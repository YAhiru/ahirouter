<?php

declare(strict_types=1);

namespace Ahiru\Router;

use Ahiru\Router\Path\Path;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function array_flip;
use function array_values;

class Definition implements RouteDefiner, RouteDefinition
{
    /** @var array<class-string<MiddlewareInterface>> */
    protected array $middlewares = [];
    protected Path $path;
    protected string $name = '';

    /**
     * @phpstan-param 'GET'|'POST'|'PATCH'|'PUT'|'DELETE'|'OPTIONS' $method
     *
     * @param class-string<RequestHandlerInterface> $requestHandler
     */
    public function __construct(
        protected string $method,
        string $path,
        protected string $requestHandler,
    ) {
        $this->path = new Path($path);
    }

    /**
     * @phpstan-param class-string<MiddlewareInterface> $middleware
     */
    public function addMiddleware(string $middleware): RouteDefiner
    {
        return $this->addMiddlewares([$middleware]);
    }

    /**
     * @param array<class-string<MiddlewareInterface>> $middlewares
     *
     * @return $this
     */
    public function addMiddlewares(array $middlewares): RouteDefiner
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    public function removeMiddleware(string $middleware): RouteDefiner
    {
        return $this->removeMiddlewares([$middleware]);
    }

    public function removeMiddlewares(array $middlewares): RouteDefiner
    {
        $middlewares = array_flip($middlewares);

        foreach ($this->middlewares as $idx => $m) {
            if (isset($middlewares[$m])) {
                unset($this->middlewares[$idx]);
            }
        }

        $this->middlewares = array_values($this->middlewares);

        return $this;
    }

    public function getRequestHandler(): string
    {
        return $this->requestHandler;
    }

    public function getAttributes(string $path): array
    {
        return $this->path->extractAttributes($path);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): Path
    {
        return $this->path;
    }

    public function setName(string $name): RouteDefiner
    {
        $this->name = $name;

        return $this;
    }

    public function hasName(): bool
    {
        return '' !== $this->getName();
    }

    /**
     * @return array<class-string<MiddlewareInterface>>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getRegex(): array
    {
        return $this->getPath()->getAttributeRegexes();
    }

    /**
     * @param array<string, string> $regexes
     *
     * @return $this
     */
    public function setRegex(array $regexes): RouteDefiner
    {
        $this->path->setAttributeRegexes($regexes);

        return $this;
    }
}
