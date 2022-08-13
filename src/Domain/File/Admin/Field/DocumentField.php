<?php

declare(strict_types=1);

namespace App\Domain\File\Admin\Field;

use App\Domain\File\Form\DocumentFormType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class DocumentField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(DocumentFormType::class)
            ->setTemplatePath('@EasyAdmin/crud/field/document.html.twig')
        ;
    }
}
