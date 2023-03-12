<?php

declare(strict_types=1);

namespace App\Domain\Competition\Config;

enum TargetType: string
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
            self::MONO_40 => 'cible de 40',
            self::MONO_60 => 'cible de 60',
            self::MONO_80 => 'cible de 80',
            self::MONO_80_REDUCED => 'cible de 80 réduit',
            self::MONO_122 => 'cible de 122',

            self::TRI_40 => 'tri-spot de 40',
            self::TRI_60 => 'tri-spot de 60',
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
}
