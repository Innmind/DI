<?php
declare(strict_types = 1);

namespace Innmind\DI;

use Innmind\DI\Exception\ServiceNotFound;

final class Container implements ServiceLocator
{
    /** @var array<string, callable(ServiceLocator): object> */
    private array $definitions = [];
    /** @var array<string, object> */
    private array $services = [];

    /**
     * This operation is immutable to prevent mixing adding definitions and
     * building already defined services.
     *
     * @param callable(ServiceLocator): object $definition
     */
    public function add(string $name, callable $definition): self
    {
        $self = clone $this;
        $self->definitions[$name] = $definition;
        $self->services = [];

        return $self;
    }

    public function __invoke(string $name): object
    {
        if (!\array_key_exists($name, $this->definitions)) {
            throw new ServiceNotFound($name);
        }

        return $this->services[$name] ?? $this->services[$name] = ($this->definitions[$name])($this);
    }
}
