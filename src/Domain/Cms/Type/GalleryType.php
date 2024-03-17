<?php

declare(strict_types=1);

namespace App\Domain\Cms\Type;

use App\Domain\File\Model\Photo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GalleryType extends AbstractType
{
    #[\Override]
    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => EntityType::class,
            'by_reference' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'entry_options' => [
                'class' => Photo::class,
                'choice_label' => 'token',
                'choice_value' => 'token',
            ],
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'gallery';
    }
}
