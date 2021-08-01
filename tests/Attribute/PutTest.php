<?php

declare(strict_types=1);

namespace Ahiru\Router\Attribute;

use Ahiru\Router\Fake\FakeMiddleware;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
final class PutTest extends TestCase
{
    private Put $route;

    protected function setUp(): void
    {
        parent::setUp();
        $this->route = new Put('/foo/{id}', 'foo', [FakeMiddleware::class], ['id' => '\d+']);
    }

    public function testGetMethod(): void
    {
        $this->assertSame('PUT', $this->route->getMethod());
    }

    public function testGetPath(): void
    {
        $this->assertSame('/foo/{id}', $this->route->getPath());
    }

    public function testGetName(): void
    {
        $this->assertSame('foo', $this->route->getName());
    }

    public function testGetMiddlewares(): void
    {
        $this->assertSame([FakeMiddleware::class], $this->route->getMiddlewares());
    }

    public function testGetRegex(): void
    {
        $this->assertSame(['id' => '\d+'], $this->route->getRegex());
    }
}
