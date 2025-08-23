<?php
declare(strict_types = 1);

namespace Tests\Innmind\DI;

use Innmind\DI\{
    Builder,
    Container,
    Exception\ServiceNotFound,
    Exception\CircularDependency,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};
use Fixtures\Innmind\DI\Services;

class ContainerTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(Container::class, Builder::new()->build());
    }

    public function testConstructingTheDefinitionsIsImmutable(): BlackBox\Proof
    {
        return $this
            ->forAll(Set::strings()->unicode())
            ->prove(function($name) {
                $container = Builder::new();
                $container2 = $container->add($name, static fn() => new \stdClass);

                $this->assertInstanceOf(Builder::class, $container2);
                $this->assertNotSame($container2, $container);
                $this->assertInstanceOf(\stdClass::class, $container2->build()($name));

                try {
                    $container->build()($name);
                    $this->fail('it should throw');
                } catch (\Exception $e) {
                    $this->assertInstanceOf(ServiceNotFound::class, $e);
                    $this->assertSame($name, $e->getMessage());
                }
            });
    }

    public function testServiceIsOnlyBuiltOnce(): BlackBox\Proof
    {
        return $this
            ->forAll(Set::strings()->unicode())
            ->prove(function($name) {
                $container = Builder::new()
                    ->add($name, static fn() => new \stdClass)
                    ->build();

                $this->assertSame($container($name), $container($name));
            });
    }

    public function testDependenciesCanBeAccesedWhenBuildingService(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set::strings()->unicode(),
                Set::strings()->unicode(),
            )
            ->filter(static fn($a, $b) => $a !== $b)
            ->prove(function($name, $dependency) {
                $container = Builder::new()
                    ->add($name, static fn($get) => $get($dependency))
                    ->add($dependency, static fn() => new \stdClass)
                    ->build();

                $this->assertSame($container($dependency), $container($name));
            });
    }

    public function testCircularDependenciesAreIntercepted(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set::strings()->unicode(),
                Set::strings()->unicode(),
            )
            ->filter(static fn($a, $b) => $a !== $b)
            ->prove(function($name, $dependency) {
                $container = Builder::new()
                    ->add($name, static fn($get) => $get($dependency))
                    ->add($dependency, static fn($get) => $get($name))
                    ->build();

                try {
                    $container($name);
                    $this->fail('it should throw');
                } catch (CircularDependency $e) {
                    $this->assertSame("$name > $dependency > $name", $e->getMessage());
                }
            });
    }

    public function testEnumCaseCanBeUsedToReferenceAService()
    {
        $expected = new \stdClass;
        $container = Builder::new()
            ->add(Services::a, static fn() => $expected)
            ->build();

        $this->assertSame($expected, $container(Services::a));
    }
}
