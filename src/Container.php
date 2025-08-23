<?php
declare(strict_types = 1);

namespace Innmind\DI;

use Innmind\DI\Exception\{
    ServiceNotFound,
    CircularDependency,
};
use Innmind\Immutable\{
    Map,
    Sequence,
    Str,
};

final class Container
{
    /**
     * @psalm-mutation-free
     *
     * @param Map<Service, callable(self): object> $definitions
     * @param Sequence<Service> $building
     * @param Map<Service, object> $services
     */
    private function __construct(
        private Map $definitions,
        private Sequence $building,
        private Map $services,
    ) {
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
        $definition = $this->definitions->get($name)->match(
            static fn($definition) => $definition,
            static fn() => throw new ServiceNotFound(\sprintf(
                '%s::%s',
                $name::class,
                $name->name,
            )),
        );

        if ($this->building->contains($name)) {
            $path = $this->building->add($name);
            $this->building = $this->building->clear();

            /** @psalm-suppress InvalidArgument */
            throw new CircularDependency(
                Str::of(' > ')
                    ->join($path->map(static fn($service) => \sprintf(
                        '%s::%s',
                        $service::class,
                        $service->name,
                    )))
                    ->toString(),
            );
        }

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->building = $this->building->add($name);

        try {
            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             * @var T
             */
            return $this
                ->services
                ->get($name)
                ->match(
                    static fn($service) => $service,
                    function() use ($name, $definition) {
                        $service = $definition($this);
                        $this->services = $this->services->put($name, $service);

                        return $service;
                    },
                );
        } finally {
            $this->building = $this->building->dropEnd(1);
        }
    }

    /**
     * @psalm-pure
     *
     * @param Map<Service, callable(self): object> $definitions
     */
    public static function of(Map $definitions): self
    {
        return new self(
            $definitions,
            Sequence::of(),
            Map::of(),
        );
    }
}
