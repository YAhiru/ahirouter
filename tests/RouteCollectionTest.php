<?php

declare(strict_types=1);

namespace Ahiru\Router;

use Ahiru\Router\Exception\NameDuplicatedException;
use Ahiru\Router\Fake\FakeHandler;
use Ahiru\Router\MatchResult\Found;
use Ahiru\Router\MatchResult\MethodNotAllowed;
use Ahiru\Router\MatchResult\NotFound;
use Laminas\Diactoros\Request;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
class RouteCollectionTest extends TestCase
{
    private RouteCollection $routes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->routes = new RouteCollection([
            new Definition('GET', '/foo/{id}', FakeHandler::class),
        ]);
    }

    public function testMatch(): void
    {
        $this->routes->add($d1 = new Definition('POST', '/foo/{id}', FakeHandler::class));
        $this->routes->add($d2 = new Definition('PUT', '/foo/{id}', FakeHandler::class));
        $this->routes->add($d3 = new Definition('PATCH', '/foo/{id}', FakeHandler::class));
        $this->routes->add($d4 = new Definition('DELETE', '/foo/{id}', FakeHandler::class));
        $this->routes->add($d5 = new Definition('OPTIONS', '/foo/{id}', FakeHandler::class));
        $this->routes->add(
            $d6 = (new Definition('GET', '/foo/{id}/bar/{id2}', FakeHandler::class))
                ->setRegex(['id' => '\d+', 'id2' => '[a-z]+'])
        );
        $this->routes->add($d7 = new Definition('GET', '/{id}/bar', FakeHandler::class));
        $this->routes->add($d8 = new Definition('GET', '/{id}/{id2}', FakeHandler::class));
        $this->routes->add($d9 = new Definition('GET', '/foo/bar', FakeHandler::class));

        $result = $this->routes->match(new Request('/foo/123', 'GET'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame(['id' => '123'], $result->getAttributes());
        $this->assertSame(FakeHandler::class, $result->getRoute()->getRequestHandler());
        $this->assertSame('GET', $result->getRoute()->getMethod());

        $result = $this->routes->match(new Request('/foo/123', 'POST'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame($d1, $result->getRoute());

        $result = $this->routes->match(new Request('/foo/123', 'PUT'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame($d2, $result->getRoute());

        $result = $this->routes->match(new Request('/foo/123', 'PATCH'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame($d3, $result->getRoute());

        $result = $this->routes->match(new Request('/foo/123', 'DELETE'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame($d4, $result->getRoute());

        $result = $this->routes->match(new Request('/foo/123', 'OPTIONS'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame($d5, $result->getRoute());

        $result = $this->routes->match(new Request('/foo/123/bar/abc', 'GET'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame($d6, $result->getRoute());

        $result = $this->routes->match(new Request('/123/bar', 'GET'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame($d7, $result->getRoute());

        $result = $this->routes->match(new Request('/123/456', 'GET'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame($d8, $result->getRoute());

        $result = $this->routes->match(new Request('/foo/bar', 'GET'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame($d9, $result->getRoute());
    }

    public function testMatchNotFound(): void
    {
        $this->routes->add(
            (new Definition('GET', '/bar/{id}', FakeHandler::class))
                ->setRegex(['id' => '\d+'])
        );

        $result = $this->routes->match(new Request('/foo/does/not/exists', 'GET'));
        $this->assertInstanceOf(NotFound::class, $result);
        $this->assertSame('/foo/does/not/exists', $result->getPath());

        $result = $this->routes->match(new Request('/bar/abc', 'GET'));
        $this->assertInstanceOf(NotFound::class, $result);
        $this->assertSame('/bar/abc', $result->getPath());
    }

    public function testMatchMethodNotAllowed(): void
    {
        $result = $this->routes->match(new Request('/foo/123', 'POST'));
        $this->assertInstanceOf(MethodNotAllowed::class, $result);
        $this->assertSame(['GET'], $result->getAllowMethods());

        $this->routes->add(new Definition('OPTIONS', '/foo/{id}', FakeHandler::class));

        $result = $this->routes->match(new Request('/foo/123', 'POST'));
        $this->assertInstanceOf(MethodNotAllowed::class, $result);
        $this->assertSame(['GET', 'OPTIONS'], $result->getAllowMethods());
    }

    public function testFindByName(): void
    {
        $this->routes->add((new Definition('GET', '/foo', FakeHandler::class))->setName('foo'));

        $this->assertInstanceOf(Definition::class, $this->routes->findByName('foo'));
        $this->assertNull($this->routes->findByName('does-not-exists'));
        $this->assertNull($this->routes->findByName(''));
    }

    public function testAdd(): void
    {
        $this->routes->add((new Definition('GET', '/foo', FakeHandler::class))->setName('foo'));
        $this->assertSame(2, $this->routes->count());

        $this->expectException(NameDuplicatedException::class);
        $this->routes->add((new Definition('GET', '/foo/baz', FakeHandler::class))->setName('foo'));
    }

    public function testCount(): void
    {
        $this->assertSame(1, $this->routes->count());
        $this->routes->add(new Definition('GET', '/foo', FakeHandler::class));

        $this->assertSame(2, $this->routes->count());
    }

    public function testMerge(): void
    {
        $this->routes->add((new Definition('GET', '/bar', FakeHandler::class))->setName('bar'));
        $r = new RouteCollection([
            (new Definition('POST', '/bar/{id}', FakeHandler::class))->setName('bar.detail'),
        ]);

        $merged = $this->routes->merge($r);

        $this->assertNotSame($this->routes, $merged);
        $this->assertInstanceOf(Found::class, $merged->match(new Request('/foo/123', 'GET')));
        $this->assertInstanceOf(Found::class, $merged->match(new Request('/bar', 'GET')));
        $this->assertInstanceOf(Found::class, $merged->match(new Request('/bar/123', 'POST')));
        $this->assertNotNull($merged->findByName('bar'));
        $this->assertNotNull($merged->findByName('bar.detail'));
    }
}
