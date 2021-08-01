<?php

declare(strict_types=1);

namespace Ahiru\Router\Attribute;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class Options extends Route
{
    /**
     * @param array<class-string<MiddlewareInterface>> $middlewares
     * @param array<string, string> $regex
     */
    public function __construct(
        string $path,
        string $name = '',
        array $middlewares = [],
        array $regex = [],
    ) {
        parent::__construct(
            method: 'OPTIONS',
            path: $path,
            name: $name,
            middlewares: $middlewares,
            regex: $regex
        );
    }
}
