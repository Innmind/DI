<?php
declare(strict_types = 1);

namespace Innmind\DI;

use Innmind\DI\Exception\ServiceNotFound;

interface ServiceLocator
{
    /**
     * @throws ServiceNotFound
     */
    public function __invoke(string $name): object;
}
