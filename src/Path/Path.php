<?php

declare(strict_types=1);

namespace Ahiru\Router\Path;

use Ahiru\Router\Exception\NotFoundAttributeException;
use InvalidArgumentException;
use function array_filter;
use function array_map;
use function explode;
use function implode;
use function sprintf;
use function trim;

class Path
{
    /** @var array<PathSegment> */
    protected array $segments;

    public function __construct(
        protected string $path,
    ) {
        $this->path = '/'.implode('/', self::explodePath($this->path));
        $this->setSegments($path);
    }

    /**
     * @param array<string, string> $attributes
     */
    public function toString(array $attributes = []): string
    {
        if ([] === $attributes) {
            return $this->path;
        }

        $path = '/';

        foreach ($this->segments as $segment) {
            if ($segment->isStatic()) {
                $path .= $segment->getName();
            } elseif (isset($attributes[$segment->getName()])) {
                $path .= $attributes[$segment->getName()];
            } else {
                $path .= '{'.$segment->getName().'}';
            }

            $path .= '/';
        }

        return rtrim($path, '/');
    }

    /**
     * @param array<string, string> $regexes
     */
    public function setAttributeRegexes(array $regexes): void
    {
        $dynamicSegments = $this->getDynamicSegments();

        foreach ($regexes as $attributeName => $regex) {
            $segment = $dynamicSegments[$attributeName] ?? null;

            if (null === $segment) {
                $names = implode(
                    ', ',
                    array_map(
                        fn (PathSegment $e): string => $e->getName(),
                        $dynamicSegments
                    )
                );

                throw new NotFoundAttributeException(sprintf('%s is not found. available names are %s.', $attributeName, $names));
            }

            $segment->setRegex($regex);
        }
    }

    /**
     * @return array<string, string>
     */
    public function getAttributeRegexes(): array
    {
        $regexes = [];

        foreach ($this->segments as $segment) {
            if ($segment->hasRegex()) {
                $regexes[$segment->getName()] = $segment->getRegex();
            }
        }

        return $regexes;
    }

    public function isMatch(string $path): bool
    {
        $segments = self::explodePath($path);

        if (count($this->segments) !== count($segments)) {
            return false;
        }

        foreach ($this->segments as $idx => $segment) {
            if ($segment->isMatch($segments[$idx])) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    public function extractAttributes(string $path): array
    {
        $segments = self::explodePath($path);

        if (count($this->segments) !== count($segments)) {
            return [];
        }

        $attributes = [];

        foreach ($this->segments as $idx => $segment) {
            if ($segment->isStatic()) {
                if ($segment->getName() !== $segments[$idx]) {
                    return [];
                }

                continue;
            }

            if (! $segment->isMatch($segments[$idx])) {
                return [];
            }

            $attributes[$segment->getName()] = $segments[$idx];
        }

        return $attributes;
    }

    /**
     * @return array<string, PathSegment>
     */
    protected function getDynamicSegments(): array
    {
        $segments = [];

        foreach ($this->segments as $segment) {
            if ($segment->isDynamic()) {
                $segments[$segment->getName()] = $segment;
            }
        }

        return $segments;
    }

    protected function setSegments(string $path): void
    {
        $segments = [];
        $dynamicNames = [];

        foreach (self::explodePath($path) as $segment) {
            $p = PathSegment::of($segment);

            if ($p->isDynamic()) {
                if (isset($dynamicNames[$p->getName()])) {
                    throw new InvalidArgumentException('placeholder names must be unique. duplicated name "'.$p->getName().'"');
                }
                $dynamicNames[$p->getName()] = $p->getName();
            }

            $segments[] = $p;
        }

        $this->segments = $segments;
    }

    /**
     * @return array<string>
     */
    protected static function explodePath(string $path): array
    {
        return array_filter(
            explode('/', trim($path, '/')),
            fn (string $segment) => '' !== $segment,
        );
    }

    public function isDynamic(): bool
    {
        foreach ($this->segments as $segment) {
            if ($segment->isDynamic()) {
                return true;
            }
        }

        return false;
    }

    public function getFirstSegment(): ?PathSegment
    {
        return $this->segments[0] ?? null;
    }
}
