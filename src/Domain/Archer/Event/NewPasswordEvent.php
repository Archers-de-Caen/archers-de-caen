<?php

declare(strict_types=1);

namespace App\Domain\Archer\Event;

use App\Domain\Archer\Model\Archer;
use Symfony\Contracts\EventDispatcher\Event;

class NewPasswordEvent extends Event
{
    public const NAME = 'archer.new_password';

    public function __construct(
        protected Archer $archer,
    ) {
    }

    public function getArcher(): Archer
    {
        return $this->archer;
    }
}
