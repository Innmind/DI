<?php
declare(strict_types = 1);

namespace Innmind\DI;

use Innmind\Immutable\Map;

/**
 * @psalm-immutable
 */
final class Builder
{
    /**
     * @param Map<Service, callable(Container): object> $definitions
     */
    private function __construct(private Map $definitions)
    {
    }

    /**
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function new(): self
    {
        return new self(Map::of());
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
        return new self($this->definitions->put($name, $definition));
    }

    #[\NoDiscard]
    public function build(): Container
    {
        return Container::of($this->definitions);
    }
}
