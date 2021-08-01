<?php

declare(strict_types=1);

namespace Ahiru\Router\Path;

use function preg_match;

class PathSegment
{
    protected string $regex = '';

    protected function __construct(
        protected string $name,
        protected bool $isStatic
    ) {
    }

    public function setRegex(string $regex): void
    {
        $this->regex = $regex;
    }

    public static function of(string $segment): self
    {
        if (1 === preg_match('/\A\{([^{}]+)}\z/', $segment, $m)) {
            return new self($m[1], false);
        }

        return new self($segment, true);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    public function isDynamic(): bool
    {
        return ! $this->isStatic();
    }

    public function hasRegex(): bool
    {
        return '' !== $this->getRegex();
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    public function isMatch(string $segment): bool
    {
        if ($this->isDynamic()) {
            if (! $this->hasRegex()) {
                return true;
            }

            return 1 === preg_match('/\A'.$this->regex.'\z/', $segment);
        }

        return $segment === $this->getName();
    }
}
