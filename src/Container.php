<?php
declare(strict_types = 1);

namespace Innmind\DI;

use Innmind\DI\Exception\{
    ServiceNotFound,
    CircularDependency,
};
use Innmind\Immutable\Map;

final class Container
{
    /** @var array<string, object> */
    private array $services = [];
    /** @var list<string> */
    private array $building = [];

    /**
     * @psalm-mutation-free
     *
     * @param Map<Service, callable(self): object> $definitions
     */
    private function __construct(private Map $definitions)
    {
    }

    /**
     * @template T of object
     *
     * @param Service<T> $name
     *
     * @throws ServiceNotFound
     * @throws CircularDependency
     *
     * @return T
     */
    public function __invoke(Service $name): object
    {
        $hash = \spl_object_hash($name);
        $definition = $this->definitions->get($name)->match(
            static fn($definition) => $definition,
            static fn() => throw new ServiceNotFound($hash),
        );

        if (\in_array($hash, $this->building, true)) {
            $path = $this->building;
            $path[] = $hash;
            $this->building = [];

            /** @psalm-suppress InvalidArgument */
            throw new CircularDependency(\implode(' > ', $path));
        }

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->building[] = $hash;

        try {
            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             * @var T
             */
            return $this->services[$hash] ??= $definition($this);
        } finally {
            \array_pop($this->building);
        }
    }

    /**
     * @psalm-pure
     *
     * @param Map<Service, callable(self): object> $definitions
     */
    public static function of(Map $definitions): self
    {
        return new self($definitions);
    }
}
