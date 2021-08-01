<?php

declare(strict_types=1);

namespace Ahiru\Router\Attribute;

use Ahiru\Router\Definition;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;

class DefinitionFactory
{
    /**
     * @param array<class-string<MiddlewareInterface>> $globalMiddlewares
     */
    public function __construct(
        protected array $globalMiddlewares = []
    ) {
    }

    /**
     * @param class-string<RequestHandlerInterface> $handler
     */
    public function create(string $handler): ?Definition
    {
        $ref = new ReflectionClass($handler);

        foreach ($ref->getAttributes() as $attribute) {
            $route = $attribute->newInstance();

            /**
             * @psalm-suppress RedundantConditionGivenDocblockType
             */
            if ($route instanceof Route) {
                return $this->convertToDefinition($route, $handler);
            }
        }

        return null;
    }

    /**
     * @param class-string<RequestHandlerInterface> $handler
     */
    protected function convertToDefinition(Route $route, string $handler): Definition
    {
        return (new Definition($route->getMethod(), $route->getPath(), $handler))
            ->setRegex($route->getRegex())
            ->setName($route->getName())
            ->addMiddlewares($this->globalMiddlewares)
            ->addMiddlewares($route->getMiddlewares())
            ;
    }
}
