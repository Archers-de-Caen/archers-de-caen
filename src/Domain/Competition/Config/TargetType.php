<?php

declare(strict_types=1);

namespace App\Domain\Competition\Config;

use App\Infrastructure\Config\Enum;

enum TargetType: string implements Enum
{
    case MONO_40 = 'mono_40';
    case MONO_60 = 'mono_60';
    case MONO_80 = 'mono_80';
    case MONO_80_REDUCED = 'mono_80_reduced';
    case MONO_122 = 'mono_122';

    case TRI_40 = 'tri_40';
    case TRI_60 = 'tri_60';

    public function toString(): string
    {
        return match ($this) {
            self::MONO_40 => 'ø 40',
            self::MONO_60 => 'ø 60',
            self::MONO_80 => 'ø 80',
            self::MONO_80_REDUCED => 'ø 80 réduit',
            self::MONO_122 => 'ø 122',

            self::TRI_40 => '3*ø 40',
            self::TRI_60 => '3*ø 60',
        };
    }

    public function toArrayValue(): string
    {
        return match ($this) {
            self::MONO_40 => self::MONO_40->value,
            self::MONO_60 => self::MONO_60->value,
            self::MONO_80 => self::MONO_80->value,
            self::MONO_80_REDUCED => self::MONO_80_REDUCED->value,
            self::MONO_122 => self::MONO_122->value,
            self::TRI_40 => self::TRI_40->value,
            self::TRI_60 => self::TRI_60->value,
        };
    }

    public static function toChoices(): array
    {
        return [
            'Mono-spots' => [
                'ø 40' => self::MONO_40,
                'ø 60' => self::MONO_60,
                'ø 80' => self::MONO_80,
                'ø 80 réduit' => self::MONO_80_REDUCED,
                'ø 122' => self::MONO_122,
            ],
            'Tri-spots' => [
                'ø 40' => self::TRI_40,
                'ø 60' => self::TRI_60,
            ],
        ];
    }

    public static function toChoicesWithEnumValue(): array
    {
        return array_combine(
            array_map(static fn (self $type) => $type->toString(), self::cases()),
            array_map(static fn (self $type) => $type, self::cases())
        );
    }
}
