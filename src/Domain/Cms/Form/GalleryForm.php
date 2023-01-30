<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form;

use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Type\GalleryType;
use App\Domain\File\Model\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

final class GalleryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', VichImageType::class, [
                'required' => true,
            ])
            ->add('photos', GalleryType::class, [
                'required' => true,
                'data_class' => Photo::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gallery::class,
        ]);
    }
}
