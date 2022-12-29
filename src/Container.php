<?php
declare(strict_types = 1);

namespace Innmind\DI;

final class Container
{
    /** @var array<string, callable(ServiceLocator): object> */
    private array $definitions = [];

    /**
     * @param array<string, callable(ServiceLocator): object> $definitions
     */
    private function __construct(array $definitions)
    {
        $this->definitions = $definitions;
    }

    public static function new(): self
    {
        return new self([]);
    }

    /**
     * This operation is immutable to prevent mixing adding definitions and
     * building already defined services.
     *
     * @param callable(ServiceLocator): object $definition
     */
    public function add(string $name, callable $definition): self
    {
        $definitions = $this->definitions;
        $definitions[$name] = $definition;

        return new self($definitions);
    }

    public function build(): ServiceLocator
    {
        return ServiceLocator::of($this->definitions);
    }
}
