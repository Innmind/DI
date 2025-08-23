# Dependency Injection

[![Build Status](https://github.com/Innmind/DI/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/Innmind/DI/actions/workflows/ci.yml)
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
    Builder,
    Container,
    Service,
};

enum Services implements Service
{
    case connection;
    case connectionA;
    case connectionB;

    /**
     * @return Service<ConnectionPool>
     */
    public static function connection(): self
    {
        /** @var Service<ConnectionPool> */
        return self::connection;
    }

    /**
     * @internal
     *
     * @return Service<\PDO>
     */
    public static function connectionA(): self
    {
        /** @var Service<\PDO> */
        return self::connectionA;
    }

    /**
     * @internal
     *
     * @return Service<\PDO>
     */
    public static function connectionB(): self
    {
        /** @var Service<\PDO> */
        return self::connectionB;
    }
}

$container = Builder::new()
    ->add(Services::connection(), fn(Container $get) => new ConnectionPool( // imaginary class
        $get(Services::connectionA()),
        $get(Services::connectionB()),
    ))
    ->add(Services::connectionA(), fn() => new \PDO('mysql://localhost'))
    ->add(Services::connectionB(), fn() => new \PDO('mysql://docker'))
    ->build();

$connection = $container(Services::connection());
$connection instanceof ConnectionPool; // true
```

The `add` method accepts any `callable` that will return an `object`. This allows to use either anonymous functions for the ease of use (but have a memory impact) or callables of the form `[Service::class, 'factoryMethod']` that allows to only load the class file when the service is loaded.

> [!TIP]
> By using enums you can easily reference all the defined services in one place. If you distribute your package, users can look at the enum to see what service they can use (since you can declare `@internal` services).
>
> On top of that no more typos in the services name and the services are automatically namespaced (no collision possible between packages).

> [!NOTE]
> Named constructors are used on the enum in order to specify the class that is returned. Psalm doesn't allow to directly specify a template value on a `case`.
