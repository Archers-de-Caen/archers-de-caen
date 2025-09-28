<?php

declare(strict_types=1);

namespace App\Domain\Cms\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class CKEditorType extends AbstractType
{
    #[\Override]
    public function getParent(): string
    {
        return TextType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'ckeditor';
    }
}
