<?php

declare(strict_types=1);

namespace Ahiru\Router\MatchResult;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
class NotFoundTest extends TestCase
{
    public function testGetPath(): void
    {
        $n = new NotFound('/foo/123');

        $this->assertSame('/foo/123', $n->getPath());
    }
}
