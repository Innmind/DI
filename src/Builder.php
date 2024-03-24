<?php
declare(strict_types = 1);

namespace Innmind\DI;

/**
 * @psalm-immutable
 */
final class Builder
{
    /** @var array<string, callable(Container): object> */
    private array $definitions = [];

    /**
     * @param array<string, callable(Container): object> $definitions
     */
    private function __construct(array $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * @psalm-pure
     */
    public static function new(): self
    {
        return new self([]);
    }

    /**
     * @param string|Service $name Using a string is deprecated
     * @param callable(Container): object $definition
     */
    public function add(string|Service $name, callable $definition): self
    {
        if ($name instanceof Service) {
            $name = \spl_object_hash($name);
        }

        $definitions = $this->definitions;
        $definitions[$name] = $definition;

        return new self($definitions);
    }

    public function build(): Container
    {
        return Container::of($this->definitions);
    }
}
