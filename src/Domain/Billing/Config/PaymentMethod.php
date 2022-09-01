<?php

declare(strict_types=1);

namespace App\Domain\Billing\Config;

use App\Infrastructure\Config\Enum;

enum PaymentMethod: string implements Enum
{
    case BANK_CHECK = 'bank-check';
    case CASH = 'cash';
    case BANK_CARD = 'bank-card';
    case PASS_PORT = 'pass-port';
    case BANK_TRANSFER = 'bank-transfer';
    case NORMANDY_ASSETS = 'normandy-assets';

    public function toString(): string
    {
        return match ($this) {
            self::BANK_CHECK => 'Chèque',
            self::CASH => 'Espèces',
            self::BANK_CARD => 'Carte bancaire',
            self::PASS_PORT => 'Pass\'port',
            self::BANK_TRANSFER => 'Virement',
            self::NORMANDY_ASSETS => 'Atouts Normandie',
        };
    }

    public function toArrayValue(): string
    {
        return match ($this) {
            self::BANK_CHECK => self::BANK_CHECK->value,
            self::CASH => self::CASH->value,
            self::BANK_CARD => self::BANK_CARD->value,
            self::PASS_PORT => self::PASS_PORT->value,
            self::BANK_TRANSFER => self::BANK_TRANSFER->value,
            self::NORMANDY_ASSETS => self::NORMANDY_ASSETS->value,
        };
    }

    public static function toChoices(): array
    {
        return array_combine(
            array_map(static fn (self $paymentMethod) => $paymentMethod->toString(), self::cases()),
            array_map(static fn (self $paymentMethod) => $paymentMethod->toArrayValue(), self::cases())
        );
    }

    public static function toChoicesWithEnumValue(): array
    {
        return array_combine(
            array_map(static fn (self $paymentMethod) => $paymentMethod->toString(), self::cases()),
            array_map(static fn (self $paymentMethod) => $paymentMethod, self::cases())
        );
    }
}
