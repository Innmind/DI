<?php
declare(strict_types = 1);

namespace Innmind\DI;

use Innmind\DI\Exception\{
    ServiceNotFound,
    CircularDependency,
};

interface ServiceLocator
{
    /**
     * @throws ServiceNotFound
     * @throws CircularDependency
     */
    public function __invoke(string $name): object;
}
