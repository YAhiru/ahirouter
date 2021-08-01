<?php

declare(strict_types=1);

namespace Ahiru\Router;

use Ahiru\Router\Exception\NameDuplicatedException;
use Ahiru\Router\MatchResult\Found;
use Ahiru\Router\MatchResult\MethodNotAllowed;
use Ahiru\Router\MatchResult\NotFound;
use Psr\Http\Message\RequestInterface;
use function array_merge;
use function count;
use function explode;
use function sprintf;
use function trim;

class RouteCollection
{
    /** @var array<RouteDefinition> */
    protected array $routes = [];
    /** @var array<string, array<RouteDefinition>> */
    protected array $staticRoutes = [];
    /** @var array<string, array<RouteDefinition>> */
    protected array $dynamicRoutes = ['*' => []];
    /** @var array<string, RouteDefinition> */
    protected array $namedRoutes = [];

    /**
     * @param array<RouteDefinition> $routes
     */
    public function __construct(
        array $routes = []
    ) {
        foreach ($routes as $route) {
            $this->add($route);
        }
    }

    public function add(RouteDefinition $definition): void
    {
        $this->routes[] = $definition;

        if ($definition->getPath()->isDynamic()) {
            $e = $definition->getPath()->getFirstSegment();

            if (null === $e || $e->isDynamic()) {
                $key = '*';
            } else {
                $key = $e->getName();
            }

            $this->dynamicRoutes[$key][] = $definition;
        } else {
            $path = $definition->getPath()->toString();
            $this->staticRoutes[$path][] = $definition;
        }

        if ($definition->hasName()) {
            if (isset($this->namedRoutes[$definition->getName()])) {
                throw new NameDuplicatedException(sprintf('%s is already defined.', $definition->getName()));
            }

            $this->namedRoutes[$definition->getName()] = $definition;
        }
    }

    public function match(RequestInterface $request): Found|MethodNotAllowed|NotFound
    {
        $path = '/'.trim($request->getUri()->getPath(), '/');
        $firstSegment = explode('/', $path)[1] ?? '';
        $methodNotAllowed = [];

        $routes = $this->staticRoutes[$path] ?? null;

        if (null === $routes) {
            $routes = $this->dynamicRoutes[$firstSegment] ?? [];

            if ('*' !== $firstSegment) {
                $routes = array_merge($routes, $this->dynamicRoutes['*']);
            }
        }

        foreach ($routes as $route) {
            if (false === $route->getPath()->isMatch($path)) {
                continue;
            }

            if ($route->getMethod() === $request->getMethod()) {
                return new Found($route, $route->getAttributes($path));
            }

            $methodNotAllowed[] = $route->getMethod();
        }

        if (count($methodNotAllowed) > 0) {
            return new MethodNotAllowed($methodNotAllowed);
        }

        return new NotFound($path);
    }

    public function findByName(string $name): ?RouteDefinition
    {
        return $this->namedRoutes[$name] ?? null;
    }

    public function merge(self $collection): self
    {
        $new = clone $this;
        $new->routes = array_merge($this->routes, $collection->routes);
        $new->staticRoutes = array_merge($this->staticRoutes, $collection->staticRoutes);
        $new->dynamicRoutes = array_merge($this->dynamicRoutes, $collection->dynamicRoutes);
        $new->namedRoutes = array_merge($this->namedRoutes, $collection->namedRoutes);

        return $new;
    }

    public function count(): int
    {
        return count($this->routes);
    }
}
