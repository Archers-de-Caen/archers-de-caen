<?php

declare(strict_types=1);

namespace App\Domain\Cms\Admin\Field;

use App\Domain\Cms\Type\GalleryType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

class GalleryField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param string|false|null $label
     */
    #[\Override]
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(GalleryType::class)
            ->setColumns('')
        ;
    }
}
