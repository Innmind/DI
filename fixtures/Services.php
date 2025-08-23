<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\DI;

use Innmind\DI\Service;

/**
 * @template S of object
 * @implements Service<S>
 */
enum Services implements Service
{
    case a;
    case name;
    case dependency;

    /**
     * @return self<\Exception>
     */
    public static function a(): self
    {
        /** @var self<\Exception> */
        return self::a;
    }
}
