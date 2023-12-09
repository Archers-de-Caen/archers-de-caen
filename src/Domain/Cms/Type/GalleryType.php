<?php

declare(strict_types=1);

namespace App\Domain\Cms\Type;

use App\Domain\File\Model\Photo;
use App\Domain\File\Repository\PhotoRepository;
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
            'by_reference' => false,
            'entry_options' => [
                'class' => Photo::class,
                'choice_label' => 'token',
                'query_builder' => fn (PhotoRepository $photoRepository) => $photoRepository->createQueryBuilder('p')
                    ->select('p', 'galleryMainPhoto')
                    ->leftJoin('p.galleryMainPhoto', 'galleryMainPhoto')
                    ->where('galleryMainPhoto.id IS NOT NULL'),
            ],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'gallery';
    }
}
