<?php

declare(strict_types=1);

namespace App\Domain\Archer\Config;

enum Weapon: string
{
    case RECURVE_BOW = 'recurve_bow';
    case COMPOUND_BOW = 'compound_bow';
    case BARE_BOW = 'bare_bow';

    public function toString(): string
    {
        return match ($this) {
            self::RECURVE_BOW => 'Arc classique',
            self::COMPOUND_BOW => 'Arc à poulies',
            self::BARE_BOW => 'Arc nu',
        };
    }

    public static function createFromString(string $weapon): self
    {
        return match ($weapon) {
            'CL', 'Arc classique' => self::RECURVE_BOW,
            'CO', 'Arc à poulies', 'Arc a poulies' => self::COMPOUND_BOW,
            'BB', 'Arc nu', 'TL', 'Tir libre' => self::BARE_BOW,

            default => throw new \ValueError($weapon.' not found'),
        };
    }

    public static function getInOrder(): array
    {
        return [
            self::RECURVE_BOW,
            self::COMPOUND_BOW,
            self::BARE_BOW,
        ];
    }

    public function isRecurve(): bool
    {
        return self::RECURVE_BOW === $this;
    }

    public function isCompound(): bool
    {
        return self::COMPOUND_BOW === $this;
    }

    public function isBareBow(): bool
    {
        return self::BARE_BOW === $this;
    }
}
