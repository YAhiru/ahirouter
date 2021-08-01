<?php

declare(strict_types=1);

namespace Ahiru\Router;

use Ahiru\Router\Exception\NotFoundAttributeException;
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
class DefinitionTest extends TestCase
{
    private Definition $definition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->definition = new Definition(
            'GET',
            '/foo/{id}',
            FakeHandler::class,
        );
    }

    public function testGetMiddlewares(): void
    {
        $this->assertSame([], $this->definition->getMiddlewares());

        $this->definition->addMiddleware(FakeMiddleware::class);

        $this->assertSame([FakeMiddleware::class], $this->definition->getMiddlewares());
    }

    public function testSetRegex(): void
    {
        $this->assertSame([], $this->definition->getRegex());

        $this->assertSame(
            ['id' => '\d+'],
            $this->definition->setRegex(['id' => '\d+'])->getRegex()
        );
    }

    public function testSetRegexAllowOverwrite(): void
    {
        $this->definition->setRegex(['id' => '\d+']);
        $this->assertSame(
            ['id' => '[a-z]+'],
            $this->definition->setRegex(['id' => '[a-z]+'])->getRegex()
        );
    }

    public function testSetRegexThrowExceptionIfInvalidAttributeName(): void
    {
        $this->expectException(NotFoundAttributeException::class);
        $this->definition->setRegex(['not-found-attribute' => 'aa']);
    }

    public function testGetRequestHandler(): void
    {
        $this->assertSame(FakeHandler::class, $this->definition->getRequestHandler());
    }

    public function testAddMiddleware(): void
    {
        $this->assertSame([], $this->definition->getMiddlewares());

        $this->definition->addMiddleware(FakeMiddleware::class);
        $this->assertSame(
            [FakeMiddleware::class],
            $this->definition->getMiddlewares()
        );
    }

    public function testAddMiddlewares(): void
    {
        $this->assertSame(
            [FakeMiddleware::class, FakeMiddleware::class],
            $this->definition->addMiddlewares([FakeMiddleware::class])
                ->addMiddlewares([FakeMiddleware::class])
                ->getMiddlewares()
        );
    }

    public function testGetMethod(): void
    {
        $this->assertSame('GET', $this->definition->getMethod());
    }

    public function testGetRegex(): void
    {
        $this->assertSame([], $this->definition->getRegex());

        $this->assertSame(
            ['id' => '\d+'],
            $this->definition->setRegex(['id' => '\d+'])
                ->getRegex()
        );
    }

    public function testGetAttributes(): void
    {
        $this->assertSame(
            [],
            $this->definition->getAttributes('/not-match')
        );
        $this->assertSame(
            [],
            $this->definition->getAttributes('/foo/123/baz')
        );

        $this->assertSame(
            ['id' => 'abc'],
            $this->definition->getAttributes('/foo/abc')
        );

        $this->assertSame(
            [],
            $this->definition->setRegex(['id' => '\d+'])
                ->getAttributes('/foo/abc')
        );
    }

    public function testGetPath(): void
    {
        $this->assertSame(
            '/foo/{id}',
            $this->definition->getPath()->toString()
        );
    }

    public function testRemoveMiddleware(): void
    {
        $m = new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return new Response();
            }
        };
        $this->definition
            ->addMiddleware(FakeMiddleware::class)
            ->addMiddleware($m::class)
            ->removeMiddleware(FakeMiddleware::class)
        ;

        $this->assertSame([$m::class], $this->definition->getMiddlewares());
    }

    public function testRemoveMiddlewares(): void
    {
        $m = new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return new Response();
            }
        };
        $m2 = new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return new Response();
            }
        };

        $this->definition->addMiddlewares([$m::class, $m2::class, FakeMiddleware::class]);
        $this->definition->removeMiddlewares([$m::class, $m2::class]);

        $this->assertSame(
            [FakeMiddleware::class],
            $this->definition->getMiddlewares()
        );
    }

    public function testGetName(): void
    {
        $this->assertSame('', $this->definition->getName());

        $this->definition->setName('foo');
        $this->assertSame('foo', $this->definition->getName());
    }

    public function testSetName(): void
    {
        $this->definition->setName('foo-bar');
        $this->assertSame('foo-bar', $this->definition->getName());
    }

    public function testHasName(): void
    {
        $this->assertFalse($this->definition->hasName());

        $this->definition->setName('foo');
        $this->assertTrue($this->definition->hasName());
    }
}
