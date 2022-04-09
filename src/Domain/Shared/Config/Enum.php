<?php

declare(strict_types=1);

namespace App\Domain\Shared\Config;

interface Enum
{
    public function toString(): string;

    /**
     * @return array<string, string>
     */
    public static function toChoices(): array;

    /**
     * @return array<string, Enum>
     */
    public static function toChoicesWithEnumValue(): array;
}
