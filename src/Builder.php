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
    #[\NoDiscard]
    public static function new(): self
    {
        return new self([]);
    }

    /**
     * @template T of object
     *
     * @param Service<T> $name Using a string is deprecated
     * @param callable(Container): T $definition
     */
    #[\NoDiscard]
    public function add(Service $name, callable $definition): self
    {
        $definitions = $this->definitions;
        $definitions[\spl_object_hash($name)] = $definition;

        return new self($definitions);
    }

    #[\NoDiscard]
    public function build(): Container
    {
        return Container::of($this->definitions);
    }
}
