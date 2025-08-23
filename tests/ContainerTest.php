<?php
declare(strict_types = 1);

namespace Tests\Innmind\DI;

use Innmind\DI\{
    Builder,
    Container,
    Exception\ServiceNotFound,
    Exception\CircularDependency,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;
use Fixtures\Innmind\DI\Services;

class ContainerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Container::class, Builder::new()->build());
    }

    public function testConstructingTheDefinitionsIsImmutable()
    {
        $container = Builder::new();
        $container2 = $container->add(Services::name, static fn() => new \stdClass);

        $this->assertInstanceOf(Builder::class, $container2);
        $this->assertNotSame($container2, $container);
        $this->assertInstanceOf(\stdClass::class, $container2->build()(Services::name));

        try {
            $container->build()(Services::name);
            $this->fail('it should throw');
        } catch (\Exception $e) {
            $this->assertInstanceOf(ServiceNotFound::class, $e);
            $this->assertSame('Fixtures\Innmind\DI\Services::name', $e->getMessage());
        }
    }

    public function testServiceIsOnlyBuiltOnce()
    {
        $container = Builder::new()
            ->add(Services::name, static fn() => new \stdClass)
            ->build();

        $this->assertSame($container(Services::name), $container(Services::name));
    }

    public function testDependenciesCanBeAccesedWhenBuildingService()
    {
        $container = Builder::new()
            ->add(Services::name, static fn($get) => $get(Services::dependency))
            ->add(Services::dependency, static fn() => new \stdClass)
            ->build();

        $this->assertSame($container(Services::dependency), $container(Services::name));
    }

    public function testCircularDependenciesAreIntercepted()
    {
        $container = Builder::new()
            ->add(Services::name, static fn($get) => $get(Services::dependency))
            ->add(Services::dependency, static fn($get) => $get(Services::name))
            ->build();

        try {
            $container(Services::name);
            $this->fail('it should throw');
        } catch (CircularDependency $e) {
            $this->assertSame("Fixtures\Innmind\DI\Services::name > Fixtures\Innmind\DI\Services::dependency > Fixtures\Innmind\DI\Services::name", $e->getMessage());
        }
    }

    public function testEnumCaseCanBeUsedToReferenceAService()
    {
        $expected = new \stdClass;
        $container = Builder::new()
            ->add(Services::a, static fn() => $expected)
            ->build();

        $this->assertSame($expected, $container(Services::a));
    }

    public function testServicesAreFreedFromMemoryWhenUnused()
    {
        $called = 0;
        $container = Builder::new()
            ->add(Services::name, static function() use (&$called) {
                ++$called;

                return new \stdClass;
            })
            ->build();

        $container(Services::name);
        $this->assertInstanceOf(\stdClass::class, $container(Services::name));
        $this->assertSame(2, $called);
    }
}
