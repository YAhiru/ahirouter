<?php

declare(strict_types=1);

namespace Ahiru\Router;

use Ahiru\Router\Fake\FakeHandler;
use Ahiru\Router\Fake\FakeMiddleware;
use Ahiru\Router\MatchResult\Found;
use Laminas\Diactoros\Request;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
    }

    public function testPatch(): void
    {
        $this->router->patch('/foo', FakeHandler::class);

        $this->assertInstanceOf(
            Found::class,
            $this->router->toCollection()->match(new Request('/foo', 'PATCH'))
        );
    }

    public function testToCollection(): void
    {
        $this->assertSame(0, $this->router->toCollection()->count());

        $this->router->patch('/foo', FakeHandler::class);
        $this->assertSame(1, $this->router->toCollection()->count());
    }

    public function testMerge(): void
    {
        $this->router->patch('/foo', FakeHandler::class);
        $r = new Router();
        $r->patch('/foo/bar', FakeHandler::class);

        $this->router->merge($r);

        $this->assertSame(2, $this->router->toCollection()->count());
    }

    public function testPut(): void
    {
        $this->router->put('/foo', FakeHandler::class);

        $this->assertInstanceOf(
            Found::class,
            $this->router->toCollection()->match(new Request('/foo', 'PUT'))
        );
    }

    public function testDelete(): void
    {
        $this->router->delete('/foo', FakeHandler::class);

        $this->assertInstanceOf(
            Found::class,
            $this->router->toCollection()->match(new Request('/foo', 'DELETE'))
        );
    }

    public function testOptions(): void
    {
        $this->router->options('/foo', FakeHandler::class);

        $this->assertInstanceOf(
            Found::class,
            $this->router->toCollection()->match(new Request('/foo', 'OPTIONS'))
        );
    }

    public function testGroup(): void
    {
        $this->router->group(
            [
                'path' => '/prefix',
                'middlewares' => [FakeMiddleware::class],
            ],
            function (Router $router): void {
                $router->get('/foo', FakeHandler::class);

                $router->group(
                    ['path' => '/prefix2', 'middlewares' => [FakeMiddleware::class]],
                    function (Router $router): void {
                        $router->get('/foo', FakeHandler::class);
                    }
                );
            }
        );

        $routes = $this->router->toCollection();

        $result = $routes->match(new Request('/prefix/foo', 'GET'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame([FakeMiddleware:: class], $result->getRoute()->getMiddlewares());

        $result = $routes->match(new Request('/prefix/prefix2/foo', 'GET'));
        $this->assertInstanceOf(Found::class, $result);
        $this->assertSame([FakeMiddleware::class, FakeMiddleware::class], $result->getRoute()->getMiddlewares());
    }

    public function testGet(): void
    {
        $this->router->get('/foo', FakeHandler::class);

        $this->assertInstanceOf(
            Found::class,
            $this->router->toCollection()->match(new Request('/foo', 'GET'))
        );
    }

    public function testPost(): void
    {
        $this->router->post('/foo', FakeHandler::class);

        $this->assertInstanceOf(
            Found::class,
            $this->router->toCollection()->match(new Request('/foo', 'POST'))
        );
    }
}
