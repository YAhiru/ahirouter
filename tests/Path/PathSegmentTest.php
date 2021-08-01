<?php

declare(strict_types=1);

namespace Ahiru\Router\Path;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
class PathSegmentTest extends TestCase
{
    private PathSegment $segment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->segment = PathSegment::of('foo');
    }

    public function testIsMatch(): void
    {
        $this->assertTrue($this->segment->isMatch('foo'));
        $this->assertFalse($this->segment->isMatch('fooo'));

        $segment = PathSegment::of('{foo}');
        $this->assertTrue($segment->isMatch('123'));
        $this->assertTrue($segment->isMatch('abcdef'));

        $segment->setRegex('\d+');
        $this->assertFalse($segment->isMatch('abcdef'));
        $this->assertTrue($segment->isMatch('123'));
    }

    public function testSetRegex(): void
    {
        $this->segment->setRegex('\d+');
        $this->assertSame('\d+', $this->segment->getRegex());
        $this->segment->setRegex('[a-z]+');
        $this->assertSame('[a-z]+', $this->segment->getRegex());
    }

    public function testIsDynamic(): void
    {
        $this->assertFalse($this->segment->isDynamic());
        $this->assertTrue(PathSegment::of('{foo}')->isDynamic());
    }

    public function testIsStatic(): void
    {
        $this->assertTrue($this->segment->isStatic());
        $this->assertFalse(PathSegment::of('{foo}')->isStatic());
    }

    public function testGetName(): void
    {
        $this->assertSame('foo', $this->segment->getName());
        $this->assertSame('bar', PathSegment::of('{bar}')->getName());
    }

    public function testGetRegex(): void
    {
        $this->assertSame('', $this->segment->getRegex());

        $this->segment->setRegex('\d+');
        $this->assertSame('\d+', $this->segment->getRegex());
    }

    public function testOf(): void
    {
        $segment = PathSegment::of('foo');
        $this->assertSame('foo', $segment->getName());
        $this->assertTrue($segment->isStatic());

        $segment = PathSegment::of('{bar}');
        $this->assertSame('bar', $segment->getName());
        $this->assertFalse($segment->isStatic());
    }

    public function testHasRegex(): void
    {
        $this->assertFalse($this->segment->hasRegex());

        $this->segment->setRegex('\d+');
        $this->assertTrue($this->segment->hasRegex());
    }
}
