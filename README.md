# ahirouter

## Usage

### Router
```php
use Ahiru\Router\Router;

$router = new Router();

$router->get('/foo');

// named route
$router->post('/foo')
    ->setName('foo.create')
;

// placeholder
$router->patch('/foo/{id}');

// regex placeholder
$router->delete('/foo/{id}')
    ->setRegex(['id' => '\d+'])
;

// apply middleware
$router->get('/mypage')
    ->addMiddleware(AuthMiddleware::class)
;

// grouping
$router->group(
    ['path' => '/bar', 'middleware' => [BarMiddleware::class]],
    function (Router $router): void {
        // equals `$router->get('/bar/{id}')->addMiddleware(BarMiddleware::class);`
        $router->get('/{id}');

        // remove middleware
        $router->post('/{id}')
            ->removeMiddleware(BarMiddleware::class)
        ;
    }
);

$routes = $router->toCollection()->match($request);
```

### Using Attribute

```php
// /path/to/Handlers/FooRequestHandler.php
namespace App\Handlers;

use Ahiru\Router\Attribute\Route;

#[Route('GET', '/foo/{id}')]
class FooRequestHandler implements \Psr\Http\Server\RequestHandlerInterface
{
    public function handle(
        \Psr\Http\Message\ServerRequestInterface $request
    ): \Psr\Http\Message\ResponseInterface {
        // ...
    }
}

// index.php
$collector = new \Ahiru\Router\Attribute\AttributeRouteCollector(
    '/path/to/Handlers',
    'App\\Handlers',
    new \Ahiru\Router\Attribute\DefinitionFactory()
);

$routes = $collector->collect();

$result = $routes->match($request);
```

### Cache
```php
$cache = new \Ahiru\Router\Cache\FileCacheDriver(
    '/path/to/cache.php'
);

/** @var \Ahiru\Router\Router $router */
$cache->store($router->toCollection());

// or

/** @var \Ahiru\Router\Attribute\AttributeRouteCollector $collector */
$cache->store($collector->collect());

$routes = $cache->restore();

$result = $routes->match($request);
```