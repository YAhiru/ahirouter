<?php

declare(strict_types=1);

namespace Ahiru\Router\Fake\AttributeRouteCollectorFixtures\ValidHandlers;

use Ahiru\Router\Attribute\Post;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Post(path: '/foo')]
class FooHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }
}
