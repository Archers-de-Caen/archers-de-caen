<?php

declare(strict_types=1);

namespace App\Infrastructure\Config;

interface Enum
{
    public function toString(): string;

    /**
     * @return array<string, mixed>
     */
    public static function toChoices(): array;

    /**
     * @return array<string, Enum>
     */
    public static function toChoicesWithEnumValue(): array;
}
