<?php

declare(strict_types=1);

namespace Ahiru\Router\Fake\AttributeRouteCollectorFixtures\NoRouteAttribute;

use Ahiru\Router\Fake\FakeAttribute;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[FakeAttribute]
class OtherAttributeHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }
}
