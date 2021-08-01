<?php

declare(strict_types=1);

namespace Ahiru\Router;

use Closure;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function array_merge;
use function sprintf;

class Router
{
    protected RouteCollection $routes;

    /**
     * @param array<class-string<MiddlewareInterface>> $defaultMiddlewares
     */
    public function __construct(
        protected array $defaultMiddlewares = [],
        protected string $pathPrefix = '',
    ) {
        $this->routes = new RouteCollection();
    }

    /**
     * @param class-string<RequestHandlerInterface> $handler
     */
    public function get(string $path, string $handler): RouteDefiner
    {
        return $this->map('GET', $path, $handler);
    }

    /**
     * @param class-string<RequestHandlerInterface> $handler
     */
    public function post(string $path, string $handler): RouteDefiner
    {
        return $this->map('POST', $path, $handler);
    }

    /**
     * @param class-string<RequestHandlerInterface> $handler
     */
    public function patch(string $path, string $handler): RouteDefiner
    {
        return $this->map('PATCH', $path, $handler);
    }

    /**
     * @param class-string<RequestHandlerInterface> $handler
     */
    public function put(string $path, string $handler): RouteDefiner
    {
        return $this->map('PUT', $path, $handler);
    }

    /**
     * @param class-string<RequestHandlerInterface> $handler
     */
    public function delete(string $path, string $handler): RouteDefiner
    {
        return $this->map('DELETE', $path, $handler);
    }

    /**
     * @param class-string<RequestHandlerInterface> $handler
     */
    public function options(string $path, string $handler): RouteDefiner
    {
        return $this->map('OPTIONS', $path, $handler);
    }

    /**
     * @phpstan-param 'GET'|'POST'|'PATCH'|'PUT'|'DELETE'|'OPTIONS' $method
     *
     * @param class-string<RequestHandlerInterface> $handler
     */
    protected function map(string $method, string $path, string $handler): RouteDefiner
    {
        $d = new Definition(
            $method,
            $this->concatPathPrefix($path),
            $handler
        );

        $this->routes->add($d->addMiddlewares($this->defaultMiddlewares));

        return $d;
    }

    /**
     * @param array{middlewares?:array<class-string<MiddlewareInterface>>, path?: string} $option
     * @param Closure(self): void $callback
     */
    public function group(
        array $option,
        Closure $callback,
    ): void {
        $r = new self(
            defaultMiddlewares: array_merge($this->defaultMiddlewares, $option['middlewares'] ?? []),
            pathPrefix: $this->concatPathPrefix($option['path'] ?? ''),
        );

        $callback($r);

        $this->merge($r);
    }

    protected function concatPathPrefix(string $path): string
    {
        return sprintf('%s/%s', $this->pathPrefix, $path);
    }

    public function merge(self $router): void
    {
        $this->routes = $this->routes->merge($router->routes);
    }

    public function toCollection(): RouteCollection
    {
        return $this->routes;
    }
}
