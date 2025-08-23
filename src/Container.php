<?php
declare(strict_types = 1);

namespace Innmind\DI;

use Innmind\DI\Exception\{
    ServiceNotFound,
    CircularDependency,
};

final class Container
{
    /** @var array<string, callable(self): object> */
    private array $definitions;
    /** @var array<string, object> */
    private array $services = [];
    /** @var list<string> */
    private array $building = [];

    /**
     * @psalm-mutation-free
     *
     * @param array<string, callable(self): object> $definitions
     */
    private function __construct(array $definitions)
    {
        $this->definitions = $definitions;
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
        $name = \spl_object_hash($name);

        /** @psalm-suppress PossiblyInvalidArgument */
        if (!\array_key_exists($name, $this->definitions)) {
            /** @psalm-suppress PossiblyInvalidArgument */
            throw new ServiceNotFound($name);
        }

        if (\in_array($name, $this->building, true)) {
            $path = $this->building;
            $path[] = $name;
            $this->building = [];

            /** @psalm-suppress InvalidArgument */
            throw new CircularDependency(\implode(' > ', $path));
        }

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->building[] = $name;

        try {
            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             * @var T
             */
            return $this->services[$name] ??= ($this->definitions[$name])($this);
        } finally {
            \array_pop($this->building);
        }
    }

    /**
     * @psalm-pure
     *
     * @param array<string, callable(self): object> $definitions
     */
    public static function of(array $definitions): self
    {
        return new self($definitions);
    }
}
