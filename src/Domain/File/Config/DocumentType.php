<?php

declare(strict_types=1);

namespace App\Domain\File\Config;



enum DocumentType: string
{
    case NEWSPAPER = 'newspaper';
    case OTHER = 'other';
}
