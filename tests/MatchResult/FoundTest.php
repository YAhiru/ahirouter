<?php

declare(strict_types=1);

namespace Ahiru\Router\MatchResult;

use Ahiru\Router\Definition;
use Ahiru\Router\Fake\FakeHandler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
class FoundTest extends TestCase
{
    public function testGetRoute(): void
    {
        $d = new Definition('GET', '/foo', FakeHandler::class);
        $f = new Found($d, []);

        $this->assertSame($d, $f->getRoute());
    }

    public function testGetAttributes(): void
    {
        $d = new Definition('GET', '/foo/{id}', FakeHandler::class);
        $f = new Found($d, ['id' => '123']);

        $this->assertSame(['id' => '123'], $f->getAttributes());
    }
}
