<?php

declare(strict_types=1);

namespace App\Helper;

final class PaginatorHelper
{
    public static function pagination(int $current, int $last): array
    {
        $delta = 1;
        $left = $current - $delta;
        $right = $current + $delta + 1;
        $range = [];
        $rangeWithDots = [];
        $l = null;

        for ($page = 1; $page <= $last; ++$page) {
            if (1 === $page || $page === $last || ($page >= $left && $page < $right)) {
                $range[] = $page;
            }
        }

        foreach ($range as $page) {
            if ($l) {
                if (2 === $page - $l) {
                    $rangeWithDots[] = $l + 1;
                } elseif (1 !== $page - $l) {
                    $rangeWithDots[] = '...';
                }
            }

            $rangeWithDots[] = $page;
            $l = $page;
        }

        return $rangeWithDots;
    }
}
