<?php

declare(strict_types=1);

namespace Ahiru\Router\Fake\AttributeRouteCollectorFixtures\NoRouteAttribute;

use Ahiru\Router\Attribute\Get;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Get(path: '/foo')]
class FooHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }
}
