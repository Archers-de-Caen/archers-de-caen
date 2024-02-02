<?php

declare(strict_types=1);

namespace App\Domain\Cms\Admin\Field;

use App\Domain\Cms\Type\CKEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class CKEditorField implements FieldInterface
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
            ->setFormType(CKEditorType::class)
            ->setColumns('')
        ;
    }
}
