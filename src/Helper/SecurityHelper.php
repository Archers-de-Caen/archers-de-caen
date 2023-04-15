<?php

declare(strict_types=1);

namespace App\Helper;

class SecurityHelper
{
    public static function generateApiKey(string $prefix = null): string
    {
        // pattern key "xxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
        $random = implode('-', [
            substr(strtolower(md5(microtime().random_int(1000, 9999))), 0, 8),
            substr(strtolower(md5(microtime().random_int(1000, 9999))), 0, 4),
            substr(strtolower(md5(microtime().random_int(1000, 9999))), 0, 4),
            substr(strtolower(md5(microtime().random_int(1000, 9999))), 0, 4),
            substr(strtolower(md5(microtime().random_int(1000, 9999))), 0, 12),
        ]);

        return $prefix ? $prefix.'_'.$random : $random;
    }

    /**
     * @param int<1, max> $length
     *
     * @throws \Exception
     */
    public static function generateRandomToken(int $length = 8, ?string $prefix = null): string
    {
        $token = bin2hex(random_bytes($length));

        return $prefix ? $prefix.'_'.$token : $token;
    }
}
