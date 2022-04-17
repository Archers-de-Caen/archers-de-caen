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
        $delta = 2;
        $left = $current - $delta;
        $right = $current + $delta + 1;
        $range = [];
        $rangeWithDots = [];
        $l = null;

        for ($i = 1; $i <= $last; $i++) {
            if ($i === 1 || $i === $last || ($i >= $left && $i < $right)) {
                $range[] = $i;
            }
        }

        foreach ($range as $i) {
            if ($l) {
                if ($i - $l === 2) {
                    $rangeWithDots[] = $l + 1;
                } else if ($i - $l !== 1) {
                    $rangeWithDots[] = '...';
                }
            }

            $rangeWithDots[] = $i;
            $l = $i;
        }

        return $rangeWithDots;
    }
}
