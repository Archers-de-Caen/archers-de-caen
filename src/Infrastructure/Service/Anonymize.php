<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

class Anonymize
{
    public static function email(string $email): string
    {
        $pattern = '/^([a-zA-Z\d_\-.]+)@([a-zA-Z\d_\-.]+)\.([a-zA-Z]{2,5})$/';

        if (preg_match($pattern, $email, $matches)) {
            if (strlen($matches[1]) > 6) {
                $firstPart = $matches[1][0].str_repeat('*', strlen($matches[1]) - 2).$matches[1][-1];
            } else {
                $firstPart = str_repeat('*', strlen($matches[1]));
            }

            return sprintf('%s@%s.%s', $firstPart, $matches[2], $matches[3]);
        }

        return '******@*****.**';
    }

    public static function phone(string $phone): string
    {
        $pattern = '/^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/';

        $phone = str_replace([' ', '.', '-', '(0)'], '', $phone);

        if (preg_match($pattern, $phone)) {
            return $phone[0].$phone[1].str_repeat('*', strlen($phone) - 4).$phone[-2].$phone[-1];
        }

        return '**********';
    }
}
