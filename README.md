# di

[![Build Status](https://github.com/innmind/di/workflows/CI/badge.svg?branch=master)](https://github.com/innmind/di/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/di/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/di)
[![Type Coverage](https://shepherd.dev/github/innmind/di/coverage.svg)](https://shepherd.dev/github/innmind/di)

Minimalist dependency injection container with a single way to define services.

Also there's no cache, so no cache invalidation problems.

## Installation

```sh
composer require innmind/di
```

## Usage

```php
use Innmind\DI\{
    Container,
    ServiceLocator,
};

$container = (new Container)
    ->add('connection', fn(ServiceLocator $get) => new ConnectionPool( // imaginary class
        $get('connection_a'),
        $get('connection_b'),
    ))
    ->add('connection_a', fn() => new \PDO('mysql://localhost'))
    ->add('connection_B', fn() => new \PDO('mysql://docker'));

$connection = $container('connection');
$connection instanceof ConnectionPool; // true
```

The `add` method accepts any `callable` that will return an `object`. This allows to use either anonymus functions for the ease of use (but have a memory impact) or callables of the form `[Service::class, 'factoryMethod']` that allows to only load the class file when the service is loaded.
