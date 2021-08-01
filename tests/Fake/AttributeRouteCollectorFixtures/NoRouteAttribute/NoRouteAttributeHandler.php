<?php

declare(strict_types=1);

namespace Ahiru\Router\Fake\AttributeRouteCollectorFixtures\NoRouteAttribute;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NoRouteAttributeHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }
}
