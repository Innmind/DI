<?php
declare(strict_types = 1);

namespace Tests\Innmind\DI;

use Innmind\DI\{
    Container,
    ServiceLocator,
    Exception\ServiceNotFound,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class ContainerTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(ServiceLocator::class, new Container);
    }

    public function testConstructingTheDefinitionsIsImmutable()
    {
        $this
            ->forAll(Set\Unicode::strings())
            ->then(function($name) {
                $container = new Container;
                $container2 = $container->add($name, fn() => new \stdClass);

                $this->assertInstanceOf(Container::class, $container2);
                $this->assertNotSame($container2, $container);
                $this->assertInstanceOf(\stdClass::class, $container2($name));

                try {
                    $container($name);
                    $this->fail('it should throw');
                } catch (\Exception $e) {
                    $this->assertInstanceOf(ServiceNotFound::class, $e);
                    $this->assertSame($name, $e->getMessage());
                }
            });
    }

    public function testServiceIsOnlyBuiltOnce()
    {
        $this
            ->forAll(Set\Unicode::strings())
            ->then(function($name) {
                $container = (new Container)->add($name, fn() => new \stdClass);

                $this->assertSame($container($name), $container($name));
            });
    }

    public function testServicesAreNotKeptBetweenVersionsOfTheContainer()
    {
        $this
            ->forAll(
                Set\Unicode::strings(),
                Set\Unicode::strings(),
            )
            ->filter(fn($a, $b) => $a !== $b)
            ->then(function($a, $b) {
                $container = new Container;
                $container2 = $container->add($a, fn() => new \stdClass);
                $firstVersion = $container2($a);
                $container3 = $container2->add($b, fn() => new \stdClass);

                $this->assertNotSame($firstVersion, $container3($a));
            });
    }

    public function testDependenciesCanBeAccesedWhenBuildingService()
    {
        $this
            ->forAll(
                Set\Unicode::strings(),
                Set\Unicode::strings(),
            )
            ->filter(fn($a, $b) => $a !== $b)
            ->then(function($name, $dependency) {
                $container = (new Container)
                    ->add($name, fn($get) => $get($dependency))
                    ->add($dependency, fn() => new \stdClass);

                $this->assertSame($container($dependency), $container($name));
            });
    }
}
