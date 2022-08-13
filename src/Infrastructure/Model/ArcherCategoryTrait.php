<?php

declare(strict_types=1);

namespace App\Infrastructure\Model;

use App\Domain\Archer\Config\Category;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait ArcherCategoryTrait
{
    #[ORM\Column(type: Types::STRING, length: 191, nullable: true, enumType: Category::class)]
    private ?Category $category = null;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }
}
