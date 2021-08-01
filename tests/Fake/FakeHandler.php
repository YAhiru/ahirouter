<?php

declare(strict_types=1);

namespace Ahiru\Router\Fake;

use Ahiru\Router\Attribute\Get;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Get(path: '/fake/{id}', name: 'fake', middlewares: [FakeMiddleware::class], regex: ['id' => '\d+'])]
class FakeHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }
}
