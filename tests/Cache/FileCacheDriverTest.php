<?php

declare(strict_types=1);

namespace Ahiru\Router\Cache;

use Ahiru\Router\Definition;
use Ahiru\Router\Fake\FakeHandler;
use Ahiru\Router\MatchResult\Found;
use Ahiru\Router\RouteCollection;
use Laminas\Diactoros\Request;
use LogicException;
use PHPUnit\Framework\TestCase;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function mkdir;
use function rmdir;
use function unlink;

/**
 * @internal
 *
 * @small
 */
final class FileCacheDriverTest extends TestCase
{
    private const CACHE_FILE = __DIR__.'/tmp/test.php';
    private FileCacheDriver $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new FileCacheDriver(self::CACHE_FILE);
    }

    protected function tearDown(): void
    {
        if (file_exists(self::CACHE_FILE)) {
            unlink(self::CACHE_FILE);
        }

        if (file_exists(dirname(self::CACHE_FILE))) {
            rmdir(dirname(self::CACHE_FILE));
        }
        parent::tearDown();
    }

    public function testStore(): void
    {
        $this->assertFileDoesNotExist(self::CACHE_FILE);

        $this->cache->store(new RouteCollection());

        $this->assertFileExists(self::CACHE_FILE);
    }

    public function testOverwrite(): void
    {
        $this->cache->store(new RouteCollection());
        $before = file_get_contents(self::CACHE_FILE);

        $this->cache->store(
            new RouteCollection([
                new Definition('GET', '/foo', FakeHandler::class),
            ])
        );

        $this->assertNotSame(
            $before,
            file_get_contents(self::CACHE_FILE)
        );
    }

    public function testRestore(): void
    {
        $this->assertNull($this->cache->restore());

        $this->cache->store(new RouteCollection([
            new Definition('GET', '/foo', FakeHandler::class),
        ]));

        $routes = $this->cache->restore();
        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertInstanceOf(
            Found::class,
            $routes->match(new Request('/foo', 'GET'))
        );
    }

    public function testThrowExceptionIfCacheFileIsInvalid(): void
    {
        mkdir(dirname(self::CACHE_FILE));
        file_put_contents(self::CACHE_FILE, '');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"'.self::CACHE_FILE.'" is invalid content.');
        $this->cache->restore();
    }

    public function testDelete(): void
    {
        mkdir(dirname(self::CACHE_FILE));
        file_put_contents(self::CACHE_FILE, '');

        $this->cache->delete();
        $this->assertFileDoesNotExist(self::CACHE_FILE);
    }

    public function testDeleteWhenFileDoesNotExist(): void
    {
        $this->assertFileDoesNotExist(self::CACHE_FILE);

        $this->cache->delete();
    }
}
