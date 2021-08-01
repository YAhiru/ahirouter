<?php

declare(strict_types=1);

namespace Ahiru\Router\Attribute;

use Ahiru\Router\Definition;
use Ahiru\Router\Fake\AttributeRouteCollectorFixtures\NoRouteAttribute\OtherAttributeHandler;
use Ahiru\Router\Fake\FakeHandler;
use Ahiru\Router\Fake\FakeMiddleware;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 *
 * @small
 */
final class DefinitionFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $middleware = $this->mockMiddleware();
        $f = new DefinitionFactory([$middleware::class]);

        $d = $f->create(FakeHandler::class);

        $this->assertInstanceOf(Definition::class, $d);
        $this->assertSame('GET', $d->getMethod());
        $this->assertSame('fake', $d->getName());
        $this->assertSame('/fake/{id}', $d->getPath()->toString());
        $this->assertSame(['id' => '\d+'], $d->getRegex());
        $this->assertSame([$middleware::class, FakeMiddleware::class], $d->getMiddlewares());
        $this->assertSame(FakeHandler::class, $d->getRequestHandler());
    }

    public function testNotRouteAttributeClass(): void
    {
        $f = new DefinitionFactory();
        $this->assertNull($f->create(OtherAttributeHandler::class));
    }

    public function testNoAttributeClass(): void
    {
        $handler = new class() implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $f = new DefinitionFactory();
        $this->assertNull($f->create($handler::class));
    }

    private function mockMiddleware(): MiddlewareInterface
    {
        return new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle($request);
            }
        };
    }
}
