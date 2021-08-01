<?php

declare(strict_types=1);

namespace Ahiru\Router\MatchResult;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
class MethodNotAllowedTest extends TestCase
{
    public function testGetAllowMethods(): void
    {
        $m = new MethodNotAllowed(['GET', 'POST']);

        $this->assertSame(
            ['GET', 'POST'],
            $m->getAllowMethods()
        );
    }
}
