<?php

declare(strict_types=1);

namespace App\Helper;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;

class PaginatorHelper
{
    public static function pagination(int $current, int $last): array
    {
        $delta = 1;
        $left = $current - $delta;
        $right = $current + $delta + 1;
        $range = [];
        $rangeWithDots = [];
        $l = null;

        for ($page = 1; $page <= $last; $page++) {
            if ($page === 1 || $page === $last || ($page >= $left && $page < $right)) {
                $range[] = $page;
            }
        }

        foreach ($range as $page) {
            if ($l) {
                if ($page - $l === 2) {
                    $rangeWithDots[] = $l + 1;
                } else if ($page - $l !== 1) {
                    $rangeWithDots[] = '...';
                }
            }

            $rangeWithDots[] = $page;
            $l = $page;
        }

        return $rangeWithDots;
    }
}
