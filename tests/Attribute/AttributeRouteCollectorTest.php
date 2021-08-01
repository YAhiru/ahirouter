<?php

declare(strict_types=1);

namespace Ahiru\Router\Attribute;

use InvalidArgumentException;
use Ahiru\Router\MatchResult\Found;
use Laminas\Diactoros\Request;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
class AttributeRouteCollectorTest extends TestCase
{
    public function testCollect(): void
    {
        $collector = new AttributeRouteCollector(
            __DIR__.'/../Fake/AttributeRouteCollectorFixtures/ValidHandlers',
            'Ahiru\\Router\\Fake\\AttributeRouteCollectorFixtures\\ValidHandlers',
            new DefinitionFactory()
        );

        $routes = $collector->collect();

        $this->assertSame(3, $routes->count());
        $this->assertInstanceOf(Found::class, $routes->match(new Request('/foo', 'POST')));
        $this->assertInstanceOf(Found::class, $routes->match(new Request('/bar', 'GET')));
        $this->assertInstanceOf(Found::class, $routes->match(new Request('/baz', 'GET')));

        $collector = new AttributeRouteCollector(
            __DIR__.'/../Fake/AttributeRouteCollectorFixtures/ValidHandlers',
            '\\Ahiru\\Router\\Fake\\AttributeRouteCollectorFixtures\\ValidHandlers\\',
            new DefinitionFactory()
        );
        $this->assertSame(3, $collector->collect()->count());
    }

    public function testDontCollectNotPhpFile(): void
    {
        $collector = new AttributeRouteCollector(
            __DIR__.'/../Fake/AttributeRouteCollectorFixtures/NotPhp',
            'Ahiru\\Router\\Fake\\AttributeRouteCollectorFixtures\\NotPhp',
            new DefinitionFactory()
        );

        $routes = $collector->collect();

        $this->assertSame(1, $routes->count());
    }

    public function testDontCollectNotRequestHandlerClass(): void
    {
        $collector = new AttributeRouteCollector(
            __DIR__.'/../Fake/AttributeRouteCollectorFixtures/NotImplementRequestHandler',
            'Ahiru\\Router\\Fake\\AttributeRouteCollectorFixtures\\NotImplementRequestHandler',
            new DefinitionFactory()
        );

        $routes = $collector->collect();

        $this->assertSame(1, $routes->count());
    }

    public function testDontCollectNoRouteAttributeRequestHandler(): void
    {
        $collector = new AttributeRouteCollector(
            __DIR__.'/../Fake/AttributeRouteCollectorFixtures/NoRouteAttribute',
            'Ahiru\\Router\\Fake\\AttributeRouteCollectorFixtures\\NoRouteAttribute',
            new DefinitionFactory()
        );

        $routes = $collector->collect();

        $this->assertSame(1, $routes->count());
    }

    public function testConstructWithDoesNotExistsDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AttributeRouteCollector(__DIR__.'/does-not-exists', '', new DefinitionFactory());
    }
}
