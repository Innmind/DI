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
     * @template N of string|Service<T>
     *
     * @param N $name
     *
     * @throws ServiceNotFound
     * @throws CircularDependency
     *
     * @return (N is string ? object : T)
     */
    public function __invoke(string|Service $name): object
    {
        if ($name instanceof Service) {
            $name = \spl_object_hash($name);
        }

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
            /** @psalm-suppress InvalidPropertyAssignmentValue */
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
