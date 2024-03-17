<?php

declare(strict_types=1);

namespace App\Tests\Ressources\Services\Faker;

use Faker\Provider\Base;

final class EnumProvider extends Base
{
    /**
     * used in alice config file.
     *
     * @throws \ReflectionException
     */
    public static function enum(string $enum, string $name): ?\UnitEnum
    {
        /** @phpstan-ignore-next-line */
        $reflection = new \ReflectionEnum($enum);

        return $reflection->hasCase($name) ? $reflection->getCase($name)->getValue() : null;
    }
}
