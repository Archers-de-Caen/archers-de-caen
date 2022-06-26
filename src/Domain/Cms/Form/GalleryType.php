<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form;

use App\Domain\File\Model\Photo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryType extends AbstractType
{
    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => EntityType::class,
            'allow_add' => true,
            'by_reference' => false,
            'entry_options' => [
                'class' => Photo::class,
                'choice_value' => fn (?Photo $photo) => $photo ? $photo->getToken() : '',
           ],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'gallery';
    }
}
