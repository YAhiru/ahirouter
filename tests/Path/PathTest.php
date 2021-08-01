<?php

declare(strict_types=1);

namespace Ahiru\Router\Path;

use Ahiru\Router\Exception\NotFoundAttributeException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
class PathTest extends TestCase
{
    private Path $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = new Path('/foo/{id}/bar/{bar_id}');
    }

    public function testThrowExceptionIfDuplicatedPlaceholderName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('placeholder names must be unique. duplicated name "id"');
        new Path('/foo/{id}/bar/{id}');
    }

    public function testConstruct(): void
    {
        $p = new Path('/foo//bar/');
        $this->assertSame('/foo/bar', $p->toString());

        $p = new Path('/foo//bar/{id}');
        $this->assertSame('/foo/bar/{id}', $p->toString());
        $this->assertSame('/foo/bar/123', $p->toString(['id' => '123']));
    }

    public function testGetAttributeRegexes(): void
    {
        $this->assertSame([], $this->path->getAttributeRegexes());

        $this->path->setAttributeRegexes(['id' => '\d+']);
        $this->assertSame(['id' => '\d+'], $this->path->getAttributeRegexes());

        $this->path->setAttributeRegexes(['id' => '\d+', 'bar_id' => '\d+']);
        $this->assertSame(['id' => '\d+', 'bar_id' => '\d+'], $this->path->getAttributeRegexes());
    }

    public function testExtractAttributes(): void
    {
        $this->assertSame(
            [],
            $this->path->extractAttributes('/foo/123/bar')
        );
        $this->assertSame(
            [],
            $this->path->extractAttributes('/foo/123/bar/123/baz')
        );
        $this->assertSame(
            [],
            $this->path->extractAttributes('/bar/123/bar/123')
        );

        $this->assertSame(
            ['id' => 'abc', 'bar_id' => 'cde'],
            $this->path->extractAttributes('/foo/abc/bar/cde')
        );

        $this->path->setAttributeRegexes(['id' => '\d+']);
        $this->assertSame(
            [],
            $this->path->extractAttributes('/foo/abc/bar/cde')
        );
    }

    public function testIsDynamic(): void
    {
        $this->assertTrue($this->path->isDynamic());
        $this->assertFalse((new Path('/foo/bar'))->isDynamic());
    }

    public function testSetAttributeRegexes(): void
    {
        $this->path->setAttributeRegexes(['id' => '\d+']);
        $this->assertSame(['id' => '\d+'], $this->path->getAttributeRegexes());

        $this->expectException(NotFoundAttributeException::class);
        $this->expectExceptionMessage('foo is not found. available names are id, bar_id.');
        $this->path->setAttributeRegexes(['foo' => '\d+']);
    }

    public function testIsMatch(): void
    {
        $this->assertFalse($this->path->isMatch('/foo/abc/bar'));
        $this->assertFalse($this->path->isMatch('/foo/abc/bar/cde/baz'));
        $this->assertTrue($this->path->isMatch('/foo/abc/bar/cde'));

        $this->path->setAttributeRegexes(['id' => '\d+', 'bar_id' => '[A-Z]+']);
        $this->assertFalse($this->path->isMatch('/foo/abc/bar/ABC'));
        $this->assertFalse($this->path->isMatch('/foo/123/bar/456'));
        $this->assertTrue($this->path->isMatch('/foo/123/bar/ABC'));
    }

    public function testToString(): void
    {
        $this->assertSame('/foo/{id}/bar/{bar_id}', $this->path->toString());
        $this->assertSame('/foo/123/bar/{bar_id}', $this->path->toString(['id' => '123']));
        $this->assertSame('/foo/{id}/bar/{bar_id}', $this->path->toString(['not-exists' => '123']));
    }

    public function testGetFirstSegment(): void
    {
        $this->assertSame('foo', $this->path->getFirstSegment()?->getName());
        $this->assertNull((new Path('/'))->getFirstSegment());
    }
}
