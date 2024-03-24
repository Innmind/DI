# Dependency Injection

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
    Builder,
    Container,
};

$container = Builder::new()
    ->add('connection', fn(Container $get) => new ConnectionPool( // imaginary class
        $get('connection_a'),
        $get('connection_b'),
    ))
    ->add('connection_a', fn() => new \PDO('mysql://localhost'))
    ->add('connection_B', fn() => new \PDO('mysql://docker'))
    ->build();

$connection = $container('connection');
$connection instanceof ConnectionPool; // true
```

The `add` method accepts any `callable` that will return an `object`. This allows to use either anonymous functions for the ease of use (but have a memory impact) or callables of the form `[Service::class, 'factoryMethod']` that allows to only load the class file when the service is loaded.

### Use enums instead of strings to reference services

Using `string`s to name services when adding them via `Builder::add()` is simple but static analysis tools can't determine the type of the returned services. This results in _mixed argument_ errors that need to be suppressed.

Instead you can use enums like so:
```php
use Innmind\DI\Service;

/**
 * @template S
 * @implements Service<S>
 */
enum Services implements Service
{
    case connection;
    case connectionA;
    case connectionB;

    /**
     * @return self<ConnectionPool>
     */
    public static function connection(): self
    {
        /** @var self<ConnectionPool> */
        return self::connection;
    }

    /**
     * @internal
     *
     * @return self<\PDO>
     */
    public static function connectionA(): self
    {
        /** @var self<\PDO> */
        return self::connectionA;
    }

    /**
     * @internal
     *
     * @return self<\PDO>
     */
    public static function connectionB(): self
    {
        /** @var self<\PDO> */
        return self::connectionB;
    }
}
```

And to use it:
```php
use Innmind\DI\{
    Builder,
    Container,
};

$container = Builder::new()
    ->add(Services::connection, fn(Container $get) => new ConnectionPool( // imaginary class
        $get(Services::connectionA),
        $get(Services::connectionB),
    ))
    ->add(Services::connectionA, fn() => new \PDO('mysql://localhost'))
    ->add(Services::connectionB, fn() => new \PDO('mysql://docker'))
    ->build();

$connection = $container(Services::connection);
$connection instanceof ConnectionPool; // true
```

> [!TIP]
> By using enums you can easily reference all the defined services in one place. If you distribute your package, users can look at the enum to see what service they can use (since you can declare `@internal` services).
>
> On top of that no more typos in the services name and the services are automatically namespaced (no collision possible between packages).

> [!NOTE]
> Named constructors are used on the enum in order to specify the class that is returned. Psalm dosn't allow to directly specify a template value on a `case`.
