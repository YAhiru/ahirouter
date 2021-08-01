<?php

declare(strict_types=1);

namespace Ahiru\Router\Attribute;

use Ahiru\Router\RouteCollection;
use Ahiru\Router\RouteDefinition;
use InvalidArgumentException;
use Psr\Http\Server\RequestHandlerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use function class_exists;
use function is_a;
use function preg_match;
use function realpath;
use function sprintf;
use function str_replace;
use function trim;

class AttributeRouteCollector
{
    public function __construct(
        protected string $handlersDirectory,
        protected string $baseNamespace,
        protected DefinitionFactory $definitionFactory,
    ) {
        $realpath = realpath($this->handlersDirectory);

        if (false === $realpath) {
            throw new InvalidArgumentException(sprintf('Get realpath of "%s" failed.', $this->handlersDirectory));
        }
        $this->handlersDirectory = $realpath;
        $this->baseNamespace = trim($this->baseNamespace, '\\');
    }

    public function collect(): RouteCollection
    {
        $routes = new RouteCollection();
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->handlersDirectory),
        );

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filename = str_replace($this->handlersDirectory, '', $file->getPathname());

            if (1 !== preg_match('/(.+)\.php\z/', $filename, $m)) {
                continue;
            }

            $fqn = sprintf('\\%s%s', $this->baseNamespace, str_replace(DIRECTORY_SEPARATOR, '\\', $m[1]));

            if (class_exists($fqn)
                && is_a($fqn, RequestHandlerInterface::class, true)
                && ($d = $this->definitionFactory->create($fqn)) instanceof RouteDefinition
            ) {
                $routes->add($d);
            }
        }

        return $routes;
    }
}
