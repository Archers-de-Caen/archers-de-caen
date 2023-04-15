<?php

declare(strict_types=1);

namespace App\Domain\Cms\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CKEditorType extends AbstractType
{
    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ckeditor';
    }
}
