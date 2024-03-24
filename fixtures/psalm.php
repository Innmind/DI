<?php

namespace Fixtures\Innmind\DI;

use Innmind\DI\Builder;

$container = Builder::new()
    ->add(Services::a, static fn() => new \Exception('foo'))
    ->build();

echo $container(Services::a())->getMessage();
